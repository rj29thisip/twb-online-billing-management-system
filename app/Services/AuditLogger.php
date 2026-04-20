<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogger
{
    /**
     * Log any action.
     */
    public static function log(
        string $action,
        string $modelType,
        ?int $modelId = null,
        array $oldValues = [],
        array $newValues = [],
        ?string $description = null
    ): void {
        try {
            AuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => $action,
                'model_type'  => $modelType,
                'model_id'    => $modelId,
                'old_values'  => empty($oldValues) ? null : $oldValues,
                'new_values'  => empty($newValues) ? null : array_merge($newValues, $description ? ['_note' => $description] : []),
                'ip_address'  => request()->ip(),
                'user_agent'  => substr(request()->userAgent() ?? '', 0, 255),
            ]);
        } catch (\Exception $e) {
            // Never let audit logging crash the main app
            \Illuminate\Support\Facades\Log::warning('AuditLogger failed: ' . $e->getMessage());
        }
    }

    public static function login(): void
    {
        static::log('login', 'User', auth()->id(), [], [
            'email' => auth()->user()->email,
            'role'  => auth()->user()->role,
            'name'  => auth()->user()->name,
        ], 'User logged in');
    }

    public static function logout(): void
    {
        static::log('logout', 'User', auth()->id(), [], [
            'email' => auth()->user()->email,
            'role'  => auth()->user()->role,
        ], 'User logged out');
    }

    public static function created($model, array $attributes = []): void
    {
        static::log('created', class_basename($model), $model->getKey(), [], $attributes ?: $model->toArray());
    }

    public static function updated($model, array $dirty = []): void
    {
        $old = [];
        $new = [];
        foreach (($dirty ?: $model->getDirty()) as $key => $newVal) {
            if (in_array($key, ['updated_at', 'created_at'])) continue;
            $old[$key] = $model->getOriginal($key);
            $new[$key] = $newVal;
        }
        static::log('updated', class_basename($model), $model->getKey(), $old, $new);
    }

    public static function deleted($model): void
    {
        static::log('deleted', class_basename($model), $model->getKey(), $model->toArray(), []);
    }

    public static function invoiceGenerated($invoice): void
    {
        static::log('invoice_generated', 'Invoice', $invoice->id, [], [
            'invoice_number'   => $invoice->invoice_number,
            'customer'         => $invoice->customer->name ?? '?',
            'account_number'   => $invoice->customer->account_number ?? '?',
            'billing_period'   => $invoice->billing_period_start . ' to ' . $invoice->billing_period_end,
            'total_amount'     => $invoice->total_amount,
            'status'           => $invoice->status,
        ], 'Invoice generated');
    }

    public static function paymentRecorded($payment): void
    {
        static::log('payment_recorded', 'Payment', $payment->id, [], [
            'receipt_number'   => $payment->receipt_number,
            'invoice_number'   => $payment->invoice->invoice_number ?? '?',
            'customer'         => $payment->customer->name ?? '?',
            'amount'           => $payment->amount,
            'payment_method'   => $payment->payment_method,
            'reference_code'   => $payment->reference_code,
        ], 'Payment recorded');
    }

    public static function invoiceCancelled($invoice): void
    {
        static::log('invoice_cancelled', 'Invoice', $invoice->id, [
            'status' => 'issued',
        ], [
            'status'         => 'cancelled',
            'invoice_number' => $invoice->invoice_number,
            'customer'       => $invoice->customer->name ?? '?',
        ], 'Invoice cancelled');
    }
}
