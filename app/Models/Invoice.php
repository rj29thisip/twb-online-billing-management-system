<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'customer_id',
        'meter_id',
        'invoice_number',
        'billing_period_start',
        'billing_period_end',
        'total_usage',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'balance_due',
        'status',
        'due_date',
        'issued_at',
        'paid_at',
    ];

    protected $casts = [
        'billing_period_start' => 'date',
        'billing_period_end'   => 'date',
        'due_date'             => 'date',
        'issued_at'            => 'date',
        'paid_at'              => 'date',
        'total_usage'          => 'decimal:4',
        'subtotal'             => 'decimal:4',
        'tax_amount'           => 'decimal:4',
        'discount_amount'      => 'decimal:4',
        'total_amount'         => 'decimal:4',
        'amount_paid'          => 'decimal:4',
        'balance_due'          => 'decimal:4',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function meter(): BelongsTo
    {
        return $this->belongsTo(Meter::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && ! $this->isPaid();
    }

    public static function generateNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ym') . '-';
        $last   = static::where('invoice_number', 'like', $prefix . '%')
                        ->orderByDesc('invoice_number')
                        ->value('invoice_number');
        $seq    = $last ? ((int) substr($last, -5) + 1) : 1;

        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }
}
