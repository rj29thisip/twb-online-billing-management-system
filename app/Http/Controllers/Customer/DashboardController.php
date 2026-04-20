<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\MeterReading;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $customer = auth()->user()->customer;
        $meter    = $customer->activeMeter;

        // ── Usage this month ──────────────────────────────────────────────────
        $usageThisMonth = 0;
        if ($meter) {
            $usageThisMonth = MeterReading::where('meter_id', $meter->id)
                ->whereMonth('capture_time', now()->month)
                ->whereYear('capture_time',  now()->year)
                ->sum('usage');
        }

        // ── Daily usage chart — current month ─────────────────────────────────
        $dailyReadings = collect();
        if ($meter) {
            $dailyReadings = MeterReading::where('meter_id', $meter->id)
                ->whereMonth('capture_time', now()->month)
                ->whereYear('capture_time',  now()->year)
                ->select(
                    DB::raw('DATE(`capture_time`) as date'),
                    DB::raw('SUM(`usage`) as total')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        }

        $usageChart = [
            'labels'   => $dailyReadings->pluck('date')
                ->map(fn ($d) => Carbon::parse($d)->format('d M'))->toArray(),
            'data'     => $dailyReadings->pluck('total')
                ->map(fn ($v) => (float) $v)->toArray(),
            'rawDates' => $dailyReadings->pluck('date')->toArray(),
        ];

        // ── Latest reading ────────────────────────────────────────────────────
        $latestReading = $meter
            ? MeterReading::where('meter_id', $meter->id)
                ->orderByDesc('capture_time')
                ->first()
            : null;

        return view('customer.dashboard', compact(
            'customer',
            'meter',
            'usageThisMonth',
            'usageChart',
            'latestReading'
        ));
    }

    public function profile()
    {
        $customer = auth()->user()->customer;
        return view('customer.profile', compact('customer'));
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        auth()->user()->customer->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }
}
