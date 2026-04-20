<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\MeterReading;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsageController extends Controller
{
    public function index(Request $request)
    {
        $customer = auth()->user()->customer;
        $meter    = $customer->activeMeter;

        $month = $request->month
            ? Carbon::parse($request->month . '-01')
            : Carbon::now()->startOfMonth();

        $dailyReadings = collect();

        if ($meter) {
            $dailyReadings = MeterReading::where('meter_id', $meter->id)
                ->whereMonth('capture_time', $month->month)
                ->whereYear('capture_time', $month->year)
                ->select(
                    DB::raw('DATE(`capture_time`) as date'),
                    DB::raw('SUM(`usage`) as total'),
                    DB::raw('MAX(`value`) as max_value')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        }

        // Build 6-month selector
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $months->push(['value' => $m->format('Y-m'), 'label' => $m->format('M Y')]);
        }

        return view('customer.usage', compact('customer', 'meter', 'dailyReadings', 'month', 'months'));
    }

    /**
     * AJAX: hourly breakdown for a given date.
     */
    public function hourly(Request $request)
    {
        $request->validate(['date' => 'required|date']);

        $customer = auth()->user()->customer;
        $meter    = $customer->activeMeter;

        if (! $meter) {
            return response()->json(['labels' => [], 'data' => []]);
        }

        $readings = MeterReading::where('meter_id', $meter->id)
            ->whereDate('capture_time', $request->date)
            ->orderBy('capture_time')
            ->get();

        return response()->json([
            'labels' => $readings->map(fn ($r) => $r->capture_time->format('H:i'))->toArray(),
            'data'   => $readings->map(fn ($r) => (float) $r->usage)->toArray(),
        ]);
    }
}
