<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'customer_id',
        'receipt_number',
        'amount',
        'payment_method',
        'reference_code',
        'payment_date',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:4',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public static function generateReceiptNumber(): string
    {
        $prefix = 'RCP-' . now()->format('Ym') . '-';
        $last   = static::where('receipt_number', 'like', $prefix . '%')
                        ->orderByDesc('receipt_number')
                        ->value('receipt_number');
        $seq    = $last ? ((int) substr($last, -5) + 1) : 1;

        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }
}
