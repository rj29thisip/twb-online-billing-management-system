<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'tariff_tier_id',
        'units_from',
        'units_to',
        'quantity',
        'unit_rate',
        'line_total',
        'description',
    ];

    protected $casts = [
        'units_from' => 'decimal:4',
        'units_to'   => 'decimal:4',
        'quantity'   => 'decimal:4',
        'unit_rate'  => 'decimal:4',
        'line_total' => 'decimal:4',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function tariffTier(): BelongsTo
    {
        return $this->belongsTo(TariffTier::class);
    }
}
