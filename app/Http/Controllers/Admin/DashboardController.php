<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\MeterReading;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $now  = now();

        // ── District scope ──────────────────────────────────────────
        $isAllDistricts = $user->isAdmin() || $user->isHeadquarters() || !$user->district_id;
        $districtId     = $isAllDistricts ? null : $user->district_id;
        $districtLabel  = $user->dashboardDistrictLabel();

        // Helper: scope a Customer query
        $customerScope = function () use ($isAllDistricts, $districtId) {
            $q = Customer::query();
            if (!$isAllDistricts) {
                $q->where(fn ($s) =>
                    $s->where('district_id', $districtId)->orWhereNull('district_id')
                );
            }
            return $q;
        };

        // Helper: scope a query through customer district
        $invoiceScope = function () use ($isAllDistricts, $districtId) {
            $q = Invoice::query();
            if (!$isAllDistricts) {
                $q->whereHas('customer', fn ($c) =>
                    $c->where(fn ($s) =>
                        $s->where('district_id', $districtId)->orWhereNull('district_id')
                    )
                );
            }
            return $q;
        };

        $paymentScope = function () use ($isAllDistricts, $districtId) {
            $q = Payment::query();
            if (!$isAllDistricts) {
                $q->whereHas('customer', fn ($c) =>
                    $c->where(fn ($s) =>
                        $s->where('district_id', $districtId)->orWhereNull('district_id')
                    )
                );
            }
            return $q;
        };

        $readingScope = function () use ($isAllDistricts, $districtId) {
            $q = MeterReading::query();
            if (!$isAllDistricts) {
                $q->whereHas('meter.customer', fn ($c) =>
                    $c->where(fn ($s) =>
                        $s->where('district_id', $districtId)->orWhereNull('district_id')
                    )
                );
            }
            return $q;
        };

        // ── Date range for current month (explicit, timezone-safe) ────
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd   = $now->copy()->endOfMonth()->toDateString();
        $today      = $now->toDateString();

        // ── Stats ───────────────────────────────────────────────────
        $stats = [
            'total_customers'        => $customerScope()->count(),
            'new_customers_month'    => $customerScope()->whereBetween('created_at', [$monthStart, $monthEnd . ' 23:59:59'])->count(),
            'consumption_this_month' => round($readingScope()->whereBetween('capture_time', [$monthStart, $monthEnd . ' 23:59:59'])->sum('usage') / 1000, 2),
            'reading_count_today'    => $readingScope()->whereDate('capture_time', $today)->count(),
            'invoiced_this_month'    => $invoiceScope()->whereBetween('issued_at', [$monthStart, $monthEnd . ' 23:59:59'])->sum('total_amount'),
            'collected_this_month'   => $paymentScope()->whereBetween('payment_date', [$monthStart, $monthEnd])->sum('amount'),
            'outstanding_count'      => $invoiceScope()->whereIn('status', ['issued', 'partially_paid', 'overdue'])->count(),
            'outstanding_amount'     => $invoiceScope()->whereIn('status', ['issued', 'partially_paid', 'overdue'])->sum('balance_due'),
        ];

        // ── Consumption chart (daily this month) ───────────────────
        $dailyData = $readingScope()
            ->whereBetween('capture_time', [$monthStart, $monthEnd . ' 23:59:59'])
            ->select(DB::raw('DATE(`capture_time`) as date'), DB::raw('SUM(`usage`)/1000 as total'))
            ->groupBy('date')->orderBy('date')->get();

        $consumptionChart = [
            'labels' => $dailyData->pluck('date')->map(fn ($d) => Carbon::parse($d)->format('d'))->toArray(),
            'data'   => $dailyData->pluck('total')->map(fn ($v) => round((float) $v, 2))->toArray(),
        ];

        // ── Revenue chart (last 6 months) ──────────────────────────
        $revenueLabels = $revenueInvoiced = $revenueCollected = [];
        for ($i = 5; $i >= 0; $i--) {
            $month   = $now->copy()->subMonths($i);
            $mStart  = $month->copy()->startOfMonth()->toDateString();
            $mEnd    = $month->copy()->endOfMonth()->toDateString();
            $revenueLabels[]    = $month->format('M');
            $revenueInvoiced[]  = round($invoiceScope()->whereBetween('issued_at', [$mStart, $mEnd . ' 23:59:59'])->sum('total_amount'), 2);
            $revenueCollected[] = round($paymentScope()->whereBetween('payment_date', [$mStart, $mEnd])->sum('amount'), 2);
        }
        $revenueChart = ['labels' => $revenueLabels, 'invoiced' => $revenueInvoiced, 'collected' => $revenueCollected];

        // ── Overdue & anomalies ─────────────────────────────────────
        $overdueInvoices = $invoiceScope()->with('customer')->where('status', 'overdue')->orderBy('due_date')->limit(10)->get();
        $anomalyReadings = $readingScope()->with(['meter.customer'])->where('is_anomaly', true)->latest('capture_time')->limit(10)->get();

        return view('admin.dashboard', compact(
            'stats', 'consumptionChart', 'revenueChart',
            'overdueInvoices', 'anomalyReadings',
            'districtLabel', 'isAllDistricts'
        ));
    }

    public function exportPdf(Request $request)
    {
        $user = auth()->user();
        $isAllDistricts = $user->isAdmin() || $user->isHeadquarters() || !$user->district_id;
        $districtId     = $isAllDistricts ? null : $user->district_id;
        $districtLabel  = $user->dashboardDistrictLabel();

        $periodInput = $request->get('month');
        $period      = $periodInput ? Carbon::createFromFormat('Y-m', $periodInput)->startOfMonth() : now()->startOfMonth();
        $periodLabel    = $period->format('F Y');
        $generatedAt    = now()->format('d/m/Y');
        $generatedFull  = now()->format('d M Y, H:i');
        $periodStart    = $period->copy()->startOfMonth();
        $periodEnd      = $period->copy()->endOfMonth();

        // ── Scoped closures ─────────────────────────────────────────
        $scopeCustomer = function () use ($isAllDistricts, $districtId) {
            $q = Customer::query();
            if (!$isAllDistricts) {
                $q->where(fn ($s) =>
                    $s->where('district_id', $districtId)->orWhereNull('district_id')
                );
            }
            return $q;
        };

        $scopeInvoice = function () use ($isAllDistricts, $districtId) {
            $q = Invoice::query();
            if (!$isAllDistricts) {
                $q->whereHas('customer', fn ($c) =>
                    $c->where(fn ($s) =>
                        $s->where('district_id', $districtId)->orWhereNull('district_id')
                    )
                );
            }
            return $q;
        };

        $scopePayment = function () use ($isAllDistricts, $districtId) {
            $q = Payment::query();
            if (!$isAllDistricts) {
                $q->whereHas('customer', fn ($c) =>
                    $c->where(fn ($s) =>
                        $s->where('district_id', $districtId)->orWhereNull('district_id')
                    )
                );
            }
            return $q;
        };

        $scopeReading = function () use ($isAllDistricts, $districtId) {
            $q = MeterReading::query();
            if (!$isAllDistricts) {
                $q->whereHas('meter.customer', fn ($c) =>
                    $c->where(fn ($s) =>
                        $s->where('district_id', $districtId)->orWhereNull('district_id')
                    )
                );
            }
            return $q;
        };

        // ── Metrics ─────────────────────────────────────────────────
        $totalCustomers   = $scopeCustomer()->count();
        $newCustomers     = $scopeCustomer()->whereBetween('created_at', [$periodStart, $periodEnd])->count();
        $totalInvoiced    = $scopeInvoice()->whereBetween('issued_at', [$periodStart, $periodEnd])->sum('total_amount');
        $totalPaid        = $scopeInvoice()->whereBetween('issued_at', [$periodStart, $periodEnd])->where('status', 'paid')->sum('total_amount');
        $totalOutstanding = $scopeInvoice()->where('status', 'overdue')->sum('balance_due');
        $invoiceCount     = $scopeInvoice()->whereBetween('issued_at', [$periodStart, $periodEnd])->count();
        $paidCount        = $scopeInvoice()->whereBetween('issued_at', [$periodStart, $periodEnd])->where('status', 'paid')->count();
        $unpaidCount      = $scopeInvoice()->whereBetween('issued_at', [$periodStart, $periodEnd])->whereIn('status', ['issued', 'partially_paid', 'overdue'])->count();
        $overdueCount     = $scopeInvoice()->where('status', 'overdue')->count();
        $totalPayments    = $scopePayment()->whereBetween('payment_date', [$periodStart, $periodEnd])->sum('amount');
        $paymentCount     = $scopePayment()->whereBetween('payment_date', [$periodStart, $periodEnd])->count();
        $totalReadings    = $scopeReading()->whereBetween('capture_time', [$periodStart, $periodEnd])->count();
        $anomalyCount     = $scopeReading()->whereBetween('capture_time', [$periodStart, $periodEnd])->where('is_anomaly', true)->count();
        $totalUsage       = $scopeReading()->whereBetween('capture_time', [$periodStart, $periodEnd])->sum('usage');
        $collectionRate   = $totalInvoiced > 0 ? round(($totalPaid / $totalInvoiced) * 100, 1) : 0;

        $overdueInvoices = $scopeInvoice()->with('customer')->where('status', 'overdue')->orderBy('due_date')->limit(10)->get();

        // Top customers by usage — scope through meter->customer district
        $meterReadingBase = $scopeReading()->whereBetween('capture_time', [$periodStart, $periodEnd]);
        $topCustomersByUsage = (clone $meterReadingBase)
            ->selectRaw('meter_id, SUM(`usage`) as total_usage')
            ->groupBy('meter_id')
            ->orderByDesc('total_usage')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $row->meter = \App\Models\Meter::with('customer')->find($row->meter_id);
                return $row;
            });

        // Logo
        $logoCandidates = [
            public_path('twb_logo.png'),
            public_path('twblogo3_transparent.png'),
            public_path('images/twb-logo.png'),
            public_path('favicon-192.png'),
        ];
        $logoData = null;
        foreach ($logoCandidates as $logoPath) {
            if (file_exists($logoPath)) {
                $ext      = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
                $mime     = $ext === 'png' ? 'image/png' : ($ext === 'jpg' ? 'image/jpeg' : 'image/png');
                $logoData = "data:{$mime};base64," . base64_encode(file_get_contents($logoPath));
                break;
            }
        }

        $districtSuffix = $isAllDistricts ? '' : '-' . str_replace(' ', '_', $districtLabel);
        $filename = 'TWB-Executive-Summary-' . $period->format('Y-m') . $districtSuffix . '.pdf';

        $pdf = Pdf::loadView('pdf.dashboard-executive-summary', compact(
            'periodLabel', 'generatedAt', 'generatedFull', 'periodStart', 'periodEnd',
            'totalCustomers', 'newCustomers', 'totalInvoiced', 'totalPaid', 'totalOutstanding',
            'invoiceCount', 'paidCount', 'unpaidCount', 'overdueCount',
            'totalPayments', 'paymentCount', 'totalReadings', 'anomalyCount', 'totalUsage',
            'collectionRate', 'overdueInvoices', 'topCustomersByUsage', 'logoData',
            'districtLabel', 'isAllDistricts'
        ))
        ->setPaper('A4', 'portrait')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => false,
            'defaultFont'          => 'DejaVu Sans',
            'dpi'                  => 150,
            'enable_php'           => false,
        ]);

        AuditLogger::log('dashboard_pdf_exported', 'Dashboard', null, [], [
            'period'   => $periodLabel,
            'district' => $districtLabel,
        ], "Exported Executive Summary PDF for: {$periodLabel} [{$districtLabel}]");

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
        ]);
    }
}
