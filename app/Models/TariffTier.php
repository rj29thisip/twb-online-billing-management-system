<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TariffTier extends Model
{
    protected $fillable = [
        'name',
        'min_units',
        'max_units',
        'rate_per_unit',
        'unit_type',
        'is_active',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to'   => 'date',
        'is_active'      => 'boolean',
        'min_units'      => 'decimal:4',
        'max_units'      => 'decimal:4',
        'rate_per_unit'  => 'decimal:4',
    ];

    public static function activeForDate(Carbon $date): Collection
    {
        return static::where('is_active', true)
            ->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $date);
            })
            ->orderBy('min_units')
            ->get();
    }
}
