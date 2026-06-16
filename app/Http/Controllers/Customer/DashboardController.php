<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(protected BillingService $billing) {}

    public function index()
    {
        $customer = auth()->user()->customer;
        $billing            = $this->billing->estimatedCurrentMonthBill($customer);
        $outstandingBalance = $customer->outstandingBalance();

        $overdueInvoice = Invoice::where('customer_id', $customer->id)
            ->where('status', 'overdue')->latest('due_date')->first();

        // FIX: Only show anomaly alert for CURRENT MONTH — not stale old data
        $now = Carbon::now();
        $anomalyAlert = optional($customer->activeMeter)
            ->readings()
            ->where('is_anomaly', true)
            ->whereYear('capture_time', $now->year)
            ->whereMonth('capture_time', $now->month)
            ->latest('capture_time')
            ->first();

        $recentInvoices = $customer->invoices()->limit(5)->get();
        $lastPayment    = Payment::where('customer_id', $customer->id)->latest('payment_date')->first();

        $announcements = Announcement::where('is_published', true)
            ->where(fn($q) => $q->whereNull('publish_from')->orWhere('publish_from', '<=', now()))
            ->where(fn($q) => $q->whereNull('publish_to')->orWhere('publish_to', '>=', now()))
            ->orderByDesc('created_at')->limit(5)->get();

        $meter = $customer->activeMeter;
        $dailyReadings = collect();
        if ($meter) {
            $dailyReadings = MeterReading::where('meter_id', $meter->id)
                ->whereMonth('capture_time', now()->month)->whereYear('capture_time', now()->year)
                ->select(DB::raw('DATE(`capture_time`) as date'), DB::raw('SUM(`usage`) as total'))
                ->groupBy('date')->orderBy('date')->get();
        }

        $usageChart = [
            'labels'   => $dailyReadings->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d M'))->toArray(),
            'data'     => $dailyReadings->pluck('total')->map(fn($v) => (float) $v)->toArray(),
            'rawDates' => $dailyReadings->pluck('date')->toArray(),
        ];

        return view('customer.dashboard', compact(
            'customer', 'billing', 'outstandingBalance', 'overdueInvoice',
            'anomalyAlert', 'recentInvoices', 'lastPayment', 'announcements', 'usageChart'
        ));
    }

    public function profile()
    {
        $customer = auth()->user()->customer;
        return view('customer.profile', compact('customer'));
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate(['phone' => 'nullable|string|max:20', 'address' => 'nullable|string']);
        auth()->user()->customer->update($validated);
        return back()->with('success', 'Profile updated successfully.');
    }
}
