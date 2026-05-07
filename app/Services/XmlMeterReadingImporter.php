<?php

namespace App\Services;

use App\Models\Meter;
use App\Models\MeterReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * XmlMeterReadingImporter
 *
 * Parses Itron OpenWay AMR XML export and imports meter readings.
 *
 * Safety guarantees:
 *  1. Duplicate prevention  — unique constraint (meter_id, capture_time)
 *     + pre-import existence check to give accurate skip count.
 *  2. Crash safety          — each Channel is wrapped in its own DB transaction.
 *     A crash mid-file only rolls back the current channel; already-committed
 *     channels are preserved.
 *  3. Anomaly detection     — per-channel rolling average; flag if usage > 5× avg.
 *  4. endpoint_id normalise — strip leading zeros → 9-digit string.
 */
class XmlMeterReadingImporter
{
    /** Multiplier above average to flag as anomaly */
    private const ANOMALY_MULTIPLIER = 5;

    /** Result counters */
    private int $inserted  = 0;
    private int $skipped   = 0;  // duplicates
    private int $anomalies = 0;
    private int $notFound  = 0;  // endpoint not in meters table
    private array $errors  = [];
    private array $warnings = [];

    /* ── Public entry point ─────────────────────────────────────── */

    /**
     * @param  string $xmlPath  Absolute path to the XML file on disk
     * @return array            Summary result
     */
    public function import(string $xmlPath): array
    {
        // ── 1. Load & validate XML ────────────────────────────────
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($xmlPath);

        if ($xml === false) {
            $errs = libxml_get_errors();
            libxml_clear_errors();
            $msg = implode('; ', array_map(fn ($e) => trim($e->message), $errs));
            throw new \RuntimeException("Invalid XML file: $msg");
        }

        // ── 2. Header info (for logging) ──────────────────────────
        $system   = (string) ($xml->Header->IEE_System['Id'] ?? 'unknown');
        $created  = (string) ($xml->Header->Creation_Datetime['Datetime'] ?? '');
        $timezone = (string) ($xml->Header->Timezone['Id'] ?? 'UTC');

        Log::info("XML Import started", [
            'system'   => $system,
            'created'  => $created,
            'timezone' => $timezone,
            'file'     => basename($xmlPath),
        ]);

        // ── 3. Process each Channel ───────────────────────────────
        $channels = $xml->xpath('.//Channel');

        if (empty($channels)) {
            throw new \RuntimeException("No <Channel> elements found in XML.");
        }

        foreach ($channels as $channelIndex => $channel) {
            try {
                $this->processChannel($channel, $channelIndex + 1, $timezone);
            } catch (\Throwable $e) {
                // Log and continue with next channel — do not abort the whole import
                $msg = "Channel " . ($channelIndex + 1) . " failed: " . $e->getMessage();
                $this->errors[] = $msg;
                Log::error("XML Import channel error", [
                    'channel' => $channelIndex + 1,
                    'error'   => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);
            }
        }

        $result = [
            'inserted'  => $this->inserted,
            'skipped'   => $this->skipped,
            'anomalies' => $this->anomalies,
            'not_found' => $this->notFound,
            'errors'    => $this->errors,
            'warnings'  => $this->warnings,
            'channels'  => count($channels),
        ];

        Log::info("XML Import complete", $result);

        return $result;
    }

    /* ── Per-channel processing ─────────────────────────────────── */

    private function processChannel(
        \SimpleXMLElement $channel,
        int               $channelNumber,
        string            $xmlTimezone
    ): void {
        // ── Parse endpoint_id ─────────────────────────────────────
        $rawUOM     = (string) ($channel->ChannelID['EndPointUOMID'] ?? '');
        $endpointId = $this->parseEndpointId($rawUOM);

        if ($endpointId === '') {
            $this->warnings[] = "Channel $channelNumber: Could not parse EndPointUOMID '$rawUOM' — skipped.";
            return;
        }

        // ── Look up meter by endpoint_id ──────────────────────────
        $meter = Meter::where('endpoint_id', $endpointId)->first();

        if (!$meter) {
            $this->notFound++;
            $this->warnings[] = "Channel $channelNumber: No meter found for endpoint_id '$endpointId' — skipped.";
            Log::warning("XML Import: meter not found", ['endpoint_id' => $endpointId]);
            return;
        }

        $readings = $channel->xpath('.//Reading');

        if (empty($readings)) {
            $this->warnings[] = "Channel $channelNumber (endpoint $endpointId): No readings — skipped.";
            return;
        }

        // ── Build rows to insert ──────────────────────────────────
        // Get the last known reading value from DB for delta on first row
        $lastDbReading = MeterReading::where('meter_id', $meter->id)
            ->orderByDesc('capture_time')
            ->first();

        $prevValue    = $lastDbReading?->value;          // null if no prior readings
        $usageHistory = [];                               // for rolling anomaly avg

        // Pre-load existing capture_times for this meter to check duplicates fast
        $existingTimes = MeterReading::where('meter_id', $meter->id)
            ->pluck('capture_time')
            ->map(fn ($t) => Carbon::parse($t)->toDateTimeString())
            ->flip()   // flip to use as hash for O(1) lookup
            ->toArray();

        $rows = [];

        foreach ($readings as $reading) {
            $rawTime  = (string) ($reading['ReadingTime'] ?? '');
            $rawValue = (string) ($reading['Value'] ?? '');

            if ($rawTime === '' || $rawValue === '') {
                continue;
            }

            // ── Parse and normalise capture_time ─────────────────
            // XML is UTC. Store as UTC (Laravel app timezone handles display)
            $captureTime = Carbon::parse($rawTime, $xmlTimezone)
                ->setTimezone('UTC')
                ->toDateTimeString();

            // ── Duplicate check ───────────────────────────────────
            if (isset($existingTimes[$captureTime])) {
                $this->skipped++;
                continue;
            }

            // ── Calculate usage delta ─────────────────────────────
            $currentValue = (int) $rawValue;
            $usage        = 0;

            if ($prevValue !== null) {
                $delta = $currentValue - $prevValue;
                // Negative delta = meter rollover or bad data — treat usage as 0
                $usage = max(0, $delta);
            }

            // ── Anomaly detection ─────────────────────────────────
            $isAnomaly   = false;
            $anomalyNote = null;

            if (!empty($usageHistory) && $usage > 0) {
                $avg = array_sum($usageHistory) / count($usageHistory);
                if ($avg > 0 && $usage > ($avg * self::ANOMALY_MULTIPLIER)) {
                    $isAnomaly   = true;
                    $anomalyNote = sprintf(
                        'Usage %d L is %.1f× above average (%d L)',
                        $usage,
                        $usage / $avg,
                        (int) $avg
                    );
                }
            }

            if ($usage > 0) {
                $usageHistory[] = $usage;
                // Keep rolling window of last 24 readings for avg calculation
                if (count($usageHistory) > 24) {
                    array_shift($usageHistory);
                }
            }

            $rows[] = [
                'meter_id'      => $meter->id,
                'capture_time'  => $captureTime,
                'received_time' => $captureTime,   // XML has no separate received time
                'value'         => $currentValue,
                'usage'         => $usage,
                'register_type' => 'flow',
                'source'        => 'amr_xml',
                'is_anomaly'    => $isAnomaly,
                'anomaly_note'  => $anomalyNote,
                'created_at'    => now()->toDateTimeString(),
                'updated_at'    => now()->toDateTimeString(),
            ];

            // Mark as existing to catch within-file duplicates too
            $existingTimes[$captureTime] = true;
            $prevValue = $currentValue;
        }

        if (empty($rows)) {
            return;
        }

        // ── Insert inside a transaction (crash-safe per channel) ──
        DB::transaction(function () use ($rows, &$meter) {
            foreach ($rows as $row) {
                // Final duplicate guard at DB level using updateOrIgnore pattern.
                // This handles race conditions if two imports run simultaneously.
                $affected = DB::table('meter_readings')->insertOrIgnore($row);

                if ($affected > 0) {
                    $this->inserted++;
                    if ($row['is_anomaly']) {
                        $this->anomalies++;
                    }
                } else {
                    // DB-level duplicate (race condition)
                    $this->skipped++;
                }
            }
        });
    }

    /* ── Helpers ────────────────────────────────────────────────── */

    /**
     * Parse endpoint_id from EndPointUOMID attribute.
     *
     * Input:  "2.16.840.1.114416.17.0120206576:LiterVolume"
     * Steps:
     *   1. Take the part before ":"           → "2.16.840.1.114416.17.0120206576"
     *   2. Take the last "."-segment          → "0120206576"
     *   3. Strip leading zeros                → "120206576"  (9 digits)
     *
     * @return string  9-digit endpoint ID, or '' on failure
     */
    private function parseEndpointId(string $rawUOM): string
    {
        if ($rawUOM === '') {
            return '';
        }

        // Strip UOM suffix (":LiterVolume" etc.)
        $withoutUOM = explode(':', $rawUOM)[0];

        // Take last OID segment
        $segments = explode('.', $withoutUOM);
        $last     = end($segments);

        if ($last === '' || !ctype_digit($last)) {
            return '';
        }

        // Remove leading zeros → matches DB format "120206001"
        return ltrim($last, '0') ?: '0';
    }
}
