<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\MeterReading;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        $stats = [
            'total_customers'        => Customer::count(),
            'new_customers_month'    => Customer::whereMonth('created_at', $now->month)
                                                ->whereYear('created_at', $now->year)->count(),
            'consumption_this_month' => round(
                MeterReading::whereMonth('capture_time', $now->month)
                            ->whereYear('capture_time', $now->year)
                            ->sum('usage') / 1000,
                2
            ),
            'reading_count_today'    => MeterReading::whereDate('capture_time', $now->toDateString())->count(),
            'invoiced_this_month'    => Invoice::whereMonth('issued_at', $now->month)
                                               ->whereYear('issued_at', $now->year)
                                               ->sum('total_amount'),
            'collected_this_month'   => Payment::whereMonth('payment_date', $now->month)
                                               ->whereYear('payment_date', $now->year)
                                               ->sum('amount'),
            'outstanding_count'      => Invoice::whereIn('status', ['issued', 'partially_paid', 'overdue'])->count(),
            'outstanding_amount'     => Invoice::whereIn('status', ['issued', 'partially_paid', 'overdue'])->sum('balance_due'),
        ];

        // Daily consumption chart — current month
        $dailyData = MeterReading::whereMonth('capture_time', $now->month)
            ->whereYear('capture_time', $now->year)
            ->select(DB::raw('DATE(`capture_time`) as date'), DB::raw('SUM(`usage`)/1000 as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $consumptionChart = [
            'labels' => $dailyData->pluck('date')->map(fn ($d) => Carbon::parse($d)->format('d'))->toArray(),
            'data'   => $dailyData->pluck('total')->map(fn ($v) => round((float) $v, 2))->toArray(),
        ];

        // Revenue chart — last 6 months
        $revenueLabels = $revenueInvoiced = $revenueCollected = [];
        for ($i = 5; $i >= 0; $i--) {
            $month              = $now->copy()->subMonths($i);
            $revenueLabels[]    = $month->format('M');
            $revenueInvoiced[]  = round(Invoice::whereMonth('issued_at', $month->month)->whereYear('issued_at', $month->year)->sum('total_amount'), 2);
            $revenueCollected[] = round(Payment::whereMonth('payment_date', $month->month)->whereYear('payment_date', $month->year)->sum('amount'), 2);
        }

        $revenueChart = [
            'labels'    => $revenueLabels,
            'invoiced'  => $revenueInvoiced,
            'collected' => $revenueCollected,
        ];

        $overdueInvoices = Invoice::with('customer')
            ->where('status', 'overdue')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // Anomaly readings — with meter and customer loaded for correct link
        $anomalyReadings = MeterReading::with(['meter.customer'])
            ->where('is_anomaly', true)
            ->latest('capture_time')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'consumptionChart', 'revenueChart', 'overdueInvoices', 'anomalyReadings'
        ));
    }
}
