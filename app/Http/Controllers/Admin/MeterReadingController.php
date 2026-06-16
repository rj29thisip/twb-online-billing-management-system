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

    private function districtScope()
    {
        $user = auth()->user();
        if ($user->isAdmin() || $user->isHeadquarters() || !$user->district_id) {
            return MeterReading::query();
        }
        return MeterReading::whereHas('meter.customer', fn ($q) =>
            $q->where(fn ($s) =>
                $s->where('district_id', $user->district_id)->orWhereNull('district_id')
            )
        );
    }

    public function index(Request $request)
    {
        $query = $this->districtScope()->with(['meter.customer'])->orderByDesc('capture_time');

        // Filter: meter_id string
        if ($request->filled('meter')) {
            $meter = Meter::where('meter_id', $request->meter)->first();
            if ($meter) $query->where('meter_id', $meter->id);
        }

        // Filter: anomalies only — FIX: use boolean() to handle checkbox "1" correctly
        if ($request->boolean('anomaly')) {
            $query->where('is_anomaly', true);
        }

        // Filter: date range (date_from / date_to)
        if ($request->filled('date_from')) {
            try {
                $query->where('capture_time', '>=', Carbon::parse($request->date_from)->startOfDay());
            } catch (\Exception $e) {}
        }
        if ($request->filled('date_to')) {
            try {
                $query->where('capture_time', '<=', Carbon::parse($request->date_to)->endOfDay());
            } catch (\Exception $e) {}
        }

        $readings     = $query->paginate(50)->withQueryString();
        $anomalyCount = MeterReading::where('is_anomaly', true)->count();
        $hasFilter    = $request->filled('meter') || $request->filled('date_from')
                     || $request->filled('date_to') || $request->boolean('anomaly');

        return view('admin.readings.index', compact('readings', 'anomalyCount', 'hasFilter'));
    }

    public function importForm() { return view('admin.readings.import'); }

    public function import(Request $request)
    {
        $request->validate(['file' => ['required','file','max:20480','mimes:csv,txt,xml,text']]);
        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = strtolower($file->getMimeType() ?? '');
        $isXml = ($extension === 'xml') || str_contains($mime, 'xml')
              || (str_contains($mime, 'text/plain') && $this->fileStartsWithXml($file->getRealPath()));
        return $isXml ? $this->importXml($file->getRealPath()) : $this->importCsv($file->getRealPath());
    }

    private function importXml(string $path): \Illuminate\Http\RedirectResponse
    {
        try {
            $importer = new XmlMeterReadingImporter();
            $result   = $importer->import($path);
        } catch (\RuntimeException $e) {
            return back()->with('error', 'XML import failed: ' . $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('XML import unexpected error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Unexpected error during import. Please check the logs.');
        }
        $parts = ["{$result['inserted']} readings imported"];
        if ($result['skipped'] > 0)   $parts[] = "{$result['skipped']} skipped (duplicates)";
        if ($result['anomalies'] > 0) $parts[] = "{$result['anomalies']} anomalies flagged";
        if ($result['not_found'] > 0) $parts[] = "{$result['not_found']} channels skipped (meter not found)";
        $message = implode(', ', $parts) . '.';
        if (!empty($result['errors'])) {
            $message .= ' Errors: ' . implode(' | ', $result['errors']);
            return back()->with('error', $message);
        }
        if (!empty($result['warnings'])) $message .= ' Warnings: ' . implode(' | ', $result['warnings']);
        return redirect()->route('admin.readings.index')->with('success', 'XML import complete: ' . $message);
    }

    private function importCsv(string $path): \Illuminate\Http\RedirectResponse
    {
        $handle = fopen($path, 'r');
        if (!$handle) return back()->with('error', 'Could not open CSV file.');
        $header = fgetcsv($handle);
        if (!$header) { fclose($handle); return back()->with('error', 'CSV file is empty or malformed.'); }
        $inserted = $skipped = $anomalies = 0;
        $errors = [];
        $row = 0;
        $meterMap = Meter::pluck('id', 'meter_id')->toArray();
        $existingPairs = DB::table('meter_readings')
            ->select('meter_id', DB::raw('DATE_FORMAT(capture_time, "%Y-%m-%d %H:%i:%s") as ct'))
            ->get()->mapWithKeys(fn($r) => ["{$r->meter_id}|{$r->ct}" => true])->toArray();
        while (($cols = fgetcsv($handle)) !== false) {
            $row++;
            if (count($cols) < 4) { $errors[] = "Row $row: not enough columns."; continue; }
            [$meterIdStr, $captureTime, $value, $usage] = array_map('trim', $cols);
            if (!isset($meterMap[$meterIdStr])) { $errors[] = "Row $row: meter '$meterIdStr' not found."; continue; }
            $meterId = $meterMap[$meterIdStr];
            $captureTime = Carbon::parse($captureTime)->toDateTimeString();
            $key = "{$meterId}|{$captureTime}";
            if (isset($existingPairs[$key])) { $skipped++; continue; }
            try {
                DB::table('meter_readings')->insertOrIgnore([
                    'meter_id' => $meterId, 'capture_time' => $captureTime, 'received_time' => $captureTime,
                    'value' => (int) $value, 'usage' => (int) $usage, 'register_type' => 'flow',
                    'source' => 'csv_import', 'is_anomaly' => false,
                    'created_at' => now()->toDateTimeString(), 'updated_at' => now()->toDateTimeString(),
                ]);
                $inserted++;
                $existingPairs[$key] = true;
            } catch (\Throwable $e) { $errors[] = "Row $row: " . $e->getMessage(); }
        }
        fclose($handle);
        $msg = "{$inserted} CSV readings imported";
        if ($skipped) $msg .= ", {$skipped} skipped";
        if (!empty($errors)) {
            $msg .= '. Errors: ' . implode(' | ', array_slice($errors, 0, 5));
            if (count($errors) > 5) $msg .= ' … and ' . (count($errors) - 5) . ' more';
            return back()->with('error', $msg);
        }
        return redirect()->route('admin.readings.index')->with('success', $msg . '.');
    }

    public function manual(Request $request)
    {
        $validated = $request->validate(['meter_id' => 'required|exists:meters,id', 'capture_time' => 'required|date', 'value' => 'required|integer|min:0']);
        $meter = Meter::findOrFail($validated['meter_id']);
        $captureTime = Carbon::parse($validated['capture_time'])->format('Y-m-d H:i:s');
        $receivedTime = now()->format('Y-m-d H:i:s');
        $exists = MeterReading::where('meter_id', $meter->id)->where('capture_time', $captureTime)->exists();
        if ($exists) return back()->with('error', "A reading for meter {$meter->meter_id} at {$captureTime} already exists.");
        $prev = MeterReading::where('meter_id', $meter->id)->where('capture_time', '<', $captureTime)->orderByDesc('capture_time')->first();
        $usage = $prev ? max(0, (int) $validated['value'] - (int) $prev->value) : 0;
        $recentUsages = MeterReading::where('meter_id', $meter->id)->where('usage', '>', 0)->orderByDesc('capture_time')->limit(6)->pluck('usage')->toArray();
        $isAnomaly = false; $anomalyNote = null;
        if (!empty($recentUsages) && $usage > 0) {
            $avg = array_sum($recentUsages) / count($recentUsages);
            if ($avg > 0 && $usage > $avg * 5) {
                $isAnomaly = true;
                $anomalyNote = sprintf('Manual entry: usage %d L is %.1f× above recent average (%d L)', $usage, $usage / $avg, (int) $avg);
            }
        }
        MeterReading::create(['meter_id' => $meter->id, 'capture_time' => $captureTime, 'received_time' => $receivedTime, 'value' => (int) $validated['value'], 'usage' => $usage, 'register_type' => 'litervolume', 'source' => 'manual', 'is_anomaly' => $isAnomaly, 'anomaly_note' => $anomalyNote]);
        $msg = "Reading saved for meter {$meter->meter_id} — usage: {$usage} L.";
        if ($isAnomaly) $msg .= ' ⚠ Flagged as anomaly.';
        return back()->with('success', $msg);
    }

    public function resolveAnomaly(Request $request, MeterReading $reading)
    {
        $reading->update(['is_anomaly' => false, 'anomaly_note' => 'Resolved by '.auth()->user()->name.' on '.now()->format('d M Y H:i')]);
        return back()->with('success', 'Anomaly resolved.');
    }

    private function fileStartsWithXml(string $path): bool
    {
        $handle = fopen($path, 'r');
        if (!$handle) return false;
        $start = fread($handle, 5);
        fclose($handle);
        return str_starts_with(ltrim($start), '<?xml') || str_starts_with(ltrim($start), '<');
    }
}
