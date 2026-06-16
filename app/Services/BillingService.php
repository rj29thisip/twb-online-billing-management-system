<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\TariffTier;
use App\Models\TaxRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Preview billing for a customer without saving.
     */
    public function preview(Customer $customer, Carbon $periodStart, Carbon $periodEnd): array
    {
        $meter = $customer->activeMeter;

        if (! $meter) {
            throw new \RuntimeException("Customer {$customer->account_number} has no active meter.");
        }

        // Check if invoice already exists for this period
        $existing = Invoice::where('customer_id', $customer->id)
            ->where('billing_period_start', $periodStart->toDateString())
            ->where('billing_period_end', $periodEnd->toDateString())
            ->first();

        if ($existing) {
            throw new \RuntimeException("Invoice already exists for this billing period.");
        }

        // Get readings in period
        $readings = MeterReading::where('meter_id', $meter->id)
            ->whereBetween('capture_time', [$periodStart, $periodEnd])
            ->orderBy('capture_time')
            ->get();

        $totalUsageRaw = (float) $readings->sum('usage'); // liters
        $totalUsageM3  = $totalUsageRaw / 1000;           // cubic meters

        // Resolve tariff category from meter_type
        // meter_type values: 'residential', 'commercial'
        $meterType       = strtolower($meter->meter_type ?? 'residential');
        $tariffCategory  = $meterType === 'commercial' ? 'commercial' : 'residential';

        $tiers = TariffTier::activeForCategory($tariffCategory, $periodStart);

        if ($tiers->isEmpty()) {
            throw new \RuntimeException(
                "No active {$tariffCategory} tariff tiers found for this billing period."
            );
        }

        // Calculate tiered charges
        $items     = [];
        $subtotal  = 0;
        $remaining = $totalUsageM3;

        foreach ($tiers as $tier) {
            if ($remaining <= 0) break;

            $tierMin   = (float) $tier->min_units;
            $tierMax   = is_null($tier->max_units) ? PHP_FLOAT_MAX : (float) $tier->max_units;
            $tierRange = $tierMax - $tierMin;

            $unitsInTier = min($remaining, $tierRange);
            if ($unitsInTier <= 0) continue;

            $lineTotal = round($unitsInTier * (float) $tier->rate_per_unit, 4);
            $subtotal += $lineTotal;
            $remaining -= $unitsInTier;

            $items[] = [
                'tariff_tier_id' => $tier->id,
                'tier_name'      => $tier->name,
                'units_from'     => $tierMin,
                'units_to'       => round($tierMin + $unitsInTier, 4),
                'quantity'       => round($unitsInTier, 4),
                'unit_rate'      => (float) $tier->rate_per_unit,
                'line_total'     => $lineTotal,
                'description'    => "{$tier->name} ({$tierMin} – " . ($tier->max_units ?? '∞') . " m³)",
                'category'       => $tariffCategory,
            ];
        }

        // Tax
        $taxRate    = TaxRate::where('is_active', true)->latest('effective_from')->first();
        $taxPercent = $taxRate ? (float) $taxRate->rate_percent : 0;
        $taxAmount  = round($subtotal * ($taxPercent / 100), 4);

        $discountAmount = 0;
        $totalAmount    = round($subtotal + $taxAmount - $discountAmount, 4);
        $dueDate        = $periodEnd->copy()->addDays(21);

        return [
            'customer'        => $customer,
            'meter'           => $meter,
            'tariff_category' => $tariffCategory,
            'period_start'    => $periodStart,
            'period_end'      => $periodEnd,
            'total_usage_raw' => $totalUsageRaw,
            'total_usage_m3'  => round($totalUsageM3, 4),
            'items'           => $items,
            'subtotal'        => $subtotal,
            'tax_rate'        => $taxPercent,
            'tax_amount'      => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount'    => $totalAmount,
            'due_date'        => $dueDate,
            'reading_count'   => $readings->count(),
        ];
    }

    /**
     * Create and save invoice from a preview result.
     */
    public function createInvoice(array $preview): Invoice
    {
        return DB::transaction(function () use ($preview) {
            $invoice = Invoice::create([
                'customer_id'          => $preview['customer']->id,
                'meter_id'             => $preview['meter']->id,
                'invoice_number'       => Invoice::generateNumber(),
                'billing_period_start' => $preview['period_start']->toDateString(),
                'billing_period_end'   => $preview['period_end']->toDateString(),
                'total_usage'          => $preview['total_usage_m3'],
                'subtotal'             => $preview['subtotal'],
                'tax_amount'           => $preview['tax_amount'],
                'discount_amount'      => $preview['discount_amount'],
                'total_amount'         => $preview['total_amount'],
                'amount_paid'          => 0,
                'balance_due'          => $preview['total_amount'],
                'status'               => 'issued',
                'due_date'             => $preview['due_date']->toDateString(),
                'issued_at'            => now()->toDateString(),
            ]);

            foreach ($preview['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id'     => $invoice->id,
                    'tariff_tier_id' => $item['tariff_tier_id'],
                    'units_from'     => $item['units_from'],
                    'units_to'       => $item['units_to'],
                    'quantity'       => $item['quantity'],
                    'unit_rate'      => $item['unit_rate'],
                    'line_total'     => $item['line_total'],
                    'description'    => $item['description'],
                ]);
            }

            return $invoice;
        });
    }

    /**
     * Record a payment against an invoice.
     */
    public function recordPayment(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $payment = Payment::create([
                'invoice_id'     => $invoice->id,
                'customer_id'    => $invoice->customer_id,
                'receipt_number' => Payment::generateReceiptNumber(),
                'amount'         => $data['amount'],
                'payment_method' => $data['payment_method'],
                'reference_code' => $data['reference_code'] ?? null,
                'payment_date'   => $data['payment_date'],
                'recorded_by'    => auth()->id(),
                'notes'          => $data['notes'] ?? null,
            ]);

            $invoice->amount_paid = (float) $invoice->amount_paid + (float) $data['amount'];
            $invoice->balance_due = max(0, (float) $invoice->total_amount - (float) $invoice->amount_paid);

            if ($invoice->balance_due <= 0) {
                $invoice->status  = 'paid';
                $invoice->paid_at = $data['payment_date'];
            } else {
                $invoice->status = 'partially_paid';
            }

            $invoice->save();

            return $payment;
        });
    }

    /**
     * Mark overdue invoices — run via scheduler daily.
     */
    public function markOverdueInvoices(): int
    {
        return Invoice::whereIn('status', ['issued', 'partially_paid'])
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);
    }

    /**
     * Estimated current-month bill for customer dashboard.
     */
    public function estimatedCurrentMonthBill(Customer $customer): array
    {
        $meter = $customer->activeMeter;

        if (! $meter) {
            return ['usage_m3' => 0, 'usage_liters' => 0, 'current_bill' => 0, 'estimated_full_bill' => 0];
        }

        $periodStart   = now()->startOfMonth();
        $totalUsageLt  = (float) MeterReading::where('meter_id', $meter->id)
            ->whereBetween('capture_time', [$periodStart, now()])
            ->sum('usage');

        $totalUsageM3 = $totalUsageLt / 1000;
        $meterType2    = strtolower($meter->meter_type ?? 'residential');
        $billCategory  = $meterType2 === 'commercial' ? 'commercial' : 'residential';
        $tiers         = TariffTier::activeForCategory($billCategory, $periodStart);

        $subtotal  = 0;
        $remaining = $totalUsageM3;

        foreach ($tiers as $tier) {
            if ($remaining <= 0) break;
            $tierMax     = is_null($tier->max_units) ? PHP_FLOAT_MAX : (float) $tier->max_units;
            $unitsInTier = min($remaining, $tierMax - (float) $tier->min_units);
            if ($unitsInTier <= 0) continue;
            $subtotal  += $unitsInTier * (float) $tier->rate_per_unit;
            $remaining -= $unitsInTier;
        }

        $taxRate    = TaxRate::where('is_active', true)->latest('effective_from')->first();
        $taxPercent = $taxRate ? (float) $taxRate->rate_percent : 0;
        $total      = $subtotal * (1 + $taxPercent / 100);

        $daysElapsed   = max(1, (int) now()->format('j'));
        $daysInMonth   = (int) now()->daysInMonth;
        $estimatedFull = $total / $daysElapsed * $daysInMonth;

        return [
            'usage_m3'            => round($totalUsageM3, 4),
            'usage_liters'        => round($totalUsageLt, 0),
            'current_bill'        => round($total, 2),
            'estimated_full_bill' => round($estimatedFull, 2),
        ];
    }
}
