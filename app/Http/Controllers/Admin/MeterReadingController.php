<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meter;
use App\Models\MeterReading;
use App\Services\XmlMeterReadingImporter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MeterReadingController extends Controller
{
    /* ── Index ─────────────────────────────────────────────────── */

    public function index(Request $request)
    {
        $query = MeterReading::with(['meter.customer'])
            ->orderByDesc('capture_time');

        // Filter by meter_id string (e.g. from anomaly alert link)
        if ($request->filled('meter')) {
            $meter = Meter::where('meter_id', $request->meter)->first();
            if ($meter) {
                $query->where('meter_id', $meter->id);
            }
        }

        // Filter: anomalies only
        if ($request->boolean('anomalies')) {
            $query->where('is_anomaly', true);
        }

        // Filter: date range
        if ($request->filled('date')) {
            $query->where('capture_time', '>=', Carbon::parse($request->date)->startOfDay())
                  ->where('capture_time', '<=', Carbon::parse($request->date)->endOfDay());
        }
        if ($request->filled('to')) {
            $query->where('capture_time', '<=', Carbon::parse($request->to)->endOfDay());
        }

        $readings = $query->paginate(50)->withQueryString();

        $anomalyCount = MeterReading::where('is_anomaly', true)->count();

        return view('admin.readings.index', compact('readings', 'anomalyCount'));
    }

    /* ── Import form ────────────────────────────────────────────── */

    public function importForm()
    {
        return view('admin.readings.import');
    }

    /* ── Import (CSV or XML) ────────────────────────────────────── */

    public function import(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:20480',                          // 20 MB max
                'mimes:csv,txt,xml,text',
            ],
        ]);

        $file      = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $mime      = strtolower($file->getMimeType() ?? '');

        // Detect XML by extension or mime type
        $isXml = ($extension === 'xml')
               || str_contains($mime, 'xml')
               || str_contains($mime, 'text/plain') && $this->fileStartsWithXml($file->getRealPath());

        if ($isXml) {
            return $this->importXml($file->getRealPath());
        }

        return $this->importCsv($file->getRealPath());
    }

    /* ── XML import ─────────────────────────────────────────────── */

    private function importXml(string $path): \Illuminate\Http\RedirectResponse
    {
        try {
            $importer = new XmlMeterReadingImporter();
            $result   = $importer->import($path);

        } catch (\RuntimeException $e) {
            return back()->with('error', 'XML import failed: ' . $e->getMessage());

        } catch (\Throwable $e) {
            Log::error('XML import unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Unexpected error during import. Please check the logs.');
        }

        // Build summary message
        $parts = [];
        $parts[] = "{$result['inserted']} readings imported";

        if ($result['skipped'] > 0) {
            $parts[] = "{$result['skipped']} skipped (duplicates)";
        }
        if ($result['anomalies'] > 0) {
            $parts[] = "{$result['anomalies']} anomalies flagged";
        }
        if ($result['not_found'] > 0) {
            $parts[] = "{$result['not_found']} channels skipped (meter not found)";
        }

        $message = implode(', ', $parts) . '.';

        // If there were any errors or warnings, append them
        if (!empty($result['errors'])) {
            $message .= ' Errors: ' . implode(' | ', $result['errors']);
            return back()->with('error', $message);
        }

        if (!empty($result['warnings'])) {
            $message .= ' Warnings: ' . implode(' | ', $result['warnings']);
        }

        return redirect()
            ->route('admin.readings.index')
            ->with('success', 'XML import complete: ' . $message);
    }

    /* ── CSV import ─────────────────────────────────────────────── */

    private function importCsv(string $path): \Illuminate\Http\RedirectResponse
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            return back()->with('error', 'Could not open CSV file.');
        }

        // Skip header row
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->with('error', 'CSV file is empty or malformed.');
        }

        $inserted  = 0;
        $skipped   = 0;
        $anomalies = 0;
        $errors    = [];
        $row       = 0;

        // Pre-load all meters indexed by meter_id string for fast lookup
        $meterMap = Meter::pluck('id', 'meter_id')->toArray();

        // Pre-load existing (meter_id, capture_time) pairs to detect duplicates
        $existingPairs = DB::table('meter_readings')
            ->select('meter_id', DB::raw('DATE_FORMAT(capture_time, "%Y-%m-%d %H:%i:%s") as ct'))
            ->get()
            ->mapWithKeys(fn ($r) => ["{$r->meter_id}|{$r->ct}" => true])
            ->toArray();

        while (($cols = fgetcsv($handle)) !== false) {
            $row++;

            // Expected columns: meter_id, capture_time, value, usage
            if (count($cols) < 4) {
                $errors[] = "Row $row: not enough columns.";
                continue;
            }

            [$meterIdStr, $captureTime, $value, $usage] = array_map('trim', $cols);

            if (!isset($meterMap[$meterIdStr])) {
                $errors[] = "Row $row: meter '$meterIdStr' not found.";
                continue;
            }

            $meterId     = $meterMap[$meterIdStr];
            $captureTime = Carbon::parse($captureTime)->toDateTimeString();
            $key         = "{$meterId}|{$captureTime}";

            if (isset($existingPairs[$key])) {
                $skipped++;
                continue;
            }

            try {
                DB::table('meter_readings')->insertOrIgnore([
                    'meter_id'      => $meterId,
                    'capture_time'  => $captureTime,
                    'received_time' => $captureTime,
                    'value'         => (int) $value,
                    'usage'         => (int) $usage,
                    'register_type' => 'flow',
                    'source'        => 'csv_import',
                    'is_anomaly'    => false,
                    'created_at'    => now()->toDateTimeString(),
                    'updated_at'    => now()->toDateTimeString(),
                ]);
                $inserted++;
                $existingPairs[$key] = true;

            } catch (\Throwable $e) {
                $errors[] = "Row $row: " . $e->getMessage();
            }
        }

        fclose($handle);

        $msg = "{$inserted} CSV readings imported";
        if ($skipped)   $msg .= ", {$skipped} skipped (duplicates)";
        if ($anomalies) $msg .= ", {$anomalies} anomalies";
        if (!empty($errors)) {
            $msg .= '. Errors: ' . implode(' | ', array_slice($errors, 0, 5));
            if (count($errors) > 5) $msg .= ' … and ' . (count($errors) - 5) . ' more';
            return back()->with('error', $msg);
        }

        return redirect()->route('admin.readings.index')->with('success', $msg . '.');
    }

    /* ── Manual entry ───────────────────────────────────────────── */

    public function manual(Request $request)
    {
        $validated = $request->validate([
            'meter_id'     => 'required|exists:meters,id',
            'capture_time' => 'required|date',
            'value'        => 'required|integer|min:0',
        ]);

        $meter       = Meter::findOrFail($validated['meter_id']);
        $captureTime = Carbon::parse($validated['capture_time'])->toDateTimeString();

        // Duplicate check
        $exists = MeterReading::where('meter_id', $meter->id)
            ->where('capture_time', $captureTime)
            ->exists();

        if ($exists) {
            return back()->with('error', "A reading for meter {$meter->meter_id} at {$captureTime} already exists.");
        }

        // Calculate usage vs previous reading
        $prev  = MeterReading::where('meter_id', $meter->id)
            ->where('capture_time', '<', $captureTime)
            ->orderByDesc('capture_time')
            ->first();

        $usage = $prev ? max(0, (int) $validated['value'] - (int) $prev->value) : 0;

        // Anomaly detection vs last 6 readings
        $recentUsages = MeterReading::where('meter_id', $meter->id)
            ->where('usage', '>', 0)
            ->orderByDesc('capture_time')
            ->limit(6)
            ->pluck('usage')
            ->toArray();

        $isAnomaly   = false;
        $anomalyNote = null;
        if (!empty($recentUsages) && $usage > 0) {
            $avg = array_sum($recentUsages) / count($recentUsages);
            if ($avg > 0 && $usage > $avg * 5) {
                $isAnomaly   = true;
                $anomalyNote = sprintf(
                    'Manual entry: usage %d L is %.1f× above recent average (%d L)',
                    $usage,
                    $usage / $avg,
                    (int) $avg
                );
            }
        }

        MeterReading::create([
            'meter_id'      => $meter->id,
            'capture_time'  => $captureTime,
            'received_time' => now()->toDateTimeString(),
            'value'         => (int) $validated['value'],
            'usage'         => $usage,
            'register_type' => 'flow',
            'source'        => 'manual',
            'is_anomaly'    => $isAnomaly,
            'anomaly_note'  => $anomalyNote,
        ]);

        $msg = "Reading saved for meter {$meter->meter_id} — usage: {$usage} L.";
        if ($isAnomaly) $msg .= ' ⚠ Flagged as anomaly.';

        return back()->with('success', $msg);
    }

    /* ── Resolve anomaly ────────────────────────────────────────── */

    public function resolveAnomaly(Request $request, MeterReading $reading)
    {
        $reading->update([
            'is_anomaly'   => false,
            'anomaly_note' => 'Resolved by ' . auth()->user()->name . ' on ' . now()->format('d M Y H:i'),
        ]);

        return back()->with('success', 'Anomaly resolved.');
    }

    /* ── Helper ─────────────────────────────────────────────────── */

    private function fileStartsWithXml(string $path): bool
    {
        $handle = fopen($path, 'r');
        if (!$handle) return false;
        $start = fread($handle, 5);
        fclose($handle);
        return str_starts_with(ltrim($start), '<?xml') || str_starts_with(ltrim($start), '<');
    }
}
