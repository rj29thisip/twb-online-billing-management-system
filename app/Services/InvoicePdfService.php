<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\MeterReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfService
{
    public function stream(Invoice $invoice): \Illuminate\Http\Response
    {
        return $this->makePdf($invoice)->stream($invoice->invoice_number . '.pdf');
    }

    public function download(Invoice $invoice): \Illuminate\Http\Response
    {
        return $this->makePdf($invoice)->download($invoice->invoice_number . '.pdf');
    }

    private function makePdf(Invoice $invoice)
    {
        $data = $this->buildViewData($invoice);

        return Pdf::loadView('invoices.twb_invoice', $data)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'DejaVu Sans',
                'dpi'                  => 150,
                'enable_php'           => false,
            ]);
    }

    private function buildViewData(Invoice $invoice): array
    {
        $invoice->load(['customer', 'meter', 'items.tariffTier', 'payments']);

        $meter    = $invoice->meter;
        $customer = $invoice->customer;

        // ── Real TWB logo ─────────────────────────────────────────────────────
        $logoBase64 = $this->loadLogoBase64();

        // ── Monthly usage for chart: 6 months ending with billing period month ─
        // Always produces exactly 6 entries — zeros for months with no data
        $billingMonth = Carbon::parse($invoice->billing_period_start)->startOfMonth();
        $monthlyData  = [];

        for ($i = 5; $i >= 0; $i--) {
            $month     = $billingMonth->copy()->subMonths($i);
            $label     = $month->format('M y');   // e.g. "Jan 26"

            $usage = 0;
            if ($meter) {
                $usage = (float) MeterReading::where('meter_id', $meter->id)
                    ->whereYear('capture_time',  $month->year)
                    ->whereMonth('capture_time', $month->month)
                    ->sum('usage');
            }

            // Convert liters → thousands of liters for chart scale
            $monthlyData[] = [$label, round($usage / 1000, 2)];
        }

        // ── Historical totals for text section ────────────────────────────────
        // Sum of the 5 months BEFORE the billing month (excludes current month)
        $historyTotal = 0;
        $historyAvg   = 0;
        if ($meter) {
            $prevMonths = MeterReading::where('meter_id', $meter->id)
                ->where('capture_time', '<', $billingMonth)
                ->where('capture_time', '>=', $billingMonth->copy()->subMonths(6))
                ->select(
                    DB::raw('YEAR(`capture_time`) as yr'),
                    DB::raw('MONTH(`capture_time`) as mo'),
                    DB::raw('SUM(`usage`) as total')
                )
                ->groupBy('yr', 'mo')
                ->orderBy('yr')->orderBy('mo')
                ->get();

            $historyTotal = (int) $prevMonths->sum('total');
            $historyAvg   = $prevMonths->count() > 0
                ? (int) ($historyTotal / $prevMonths->count())
                : 0;
        }

        // ── Current month supply ──────────────────────────────────────────────
        $currentSupply = 0;
        if ($meter) {
            $currentSupply = (int) MeterReading::where('meter_id', $meter->id)
                ->whereBetween('capture_time', [
                    $invoice->billing_period_start,
                    $invoice->billing_period_end,
                ])
                ->sum('usage');
        }

        // ── Meter readings ────────────────────────────────────────────────────
        $previousReading = (int) (MeterReading::where('meter_id', $meter->id)
            ->where('capture_time', '<', $invoice->billing_period_start)
            ->orderByDesc('capture_time')
            ->value('value') ?? 0);

        $presentReading = (int) (MeterReading::where('meter_id', $meter->id)
            ->whereBetween('capture_time', [
                $invoice->billing_period_start,
                $invoice->billing_period_end,
            ])
            ->orderByDesc('capture_time')
            ->value('value') ?? 0);

        $unitsSupplied = $currentSupply;

        // ── Rate ──────────────────────────────────────────────────────────────
        $ratePerM3   = (float) ($invoice->items->first()?->unit_rate ?? 0);
        $ratePerUnit = $ratePerM3 > 0 ? $ratePerM3 / 1000 : 0.00255;

        // ── Opening balance ───────────────────────────────────────────────────
        $openingBalance = (float) Invoice::where('customer_id', $customer->id)
            ->where('id', '<', $invoice->id)
            ->whereIn('status', ['issued', 'partially_paid', 'overdue'])
            ->sum('balance_due');

        $paymentReceived = (float) $invoice->payments->sum('amount');

        // ── Discount & tax percentages ────────────────────────────────────────
        $discountPct = ($invoice->subtotal > 0 && $invoice->discount_amount > 0)
            ? (int) round(($invoice->discount_amount / $invoice->subtotal) * 100)
            : 0;

        $taxPct = ($invoice->subtotal > 0 && $invoice->tax_amount > 0)
            ? (int) round(($invoice->tax_amount / $invoice->subtotal) * 100)
            : 15;

        // ── Generate chart PNG (monthly bars, always 6 entries) ───────────────
        $chartBase64 = InvoiceImageGenerator::monthlyChartBase64($monthlyData, 400, 220);

        return compact(
            'invoice',
            'previousReading',
            'presentReading',
            'unitsSupplied',
            'ratePerUnit',
            'openingBalance',
            'paymentReceived',
            'discountPct',
            'taxPct',
            'historyTotal',
            'historyAvg',
            'currentSupply',
            'logoBase64',
            'chartBase64'
        );
    }

    private function loadLogoBase64(): string
    {
        $logoPath = public_path('twb_logo.png');
        if (file_exists($logoPath)) {
            return base64_encode(file_get_contents($logoPath));
        }
        return InvoiceImageGenerator::logoBase64(120);
    }
}
