<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TariffTier extends Model
{
    const CATEGORY_RESIDENTIAL = 'residential';
    const CATEGORY_COMMERCIAL  = 'commercial';

    protected $fillable = [
        'name',
        'category',
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

    /**
     * Get active tiers for a specific category and billing date.
     * category: 'residential' | 'commercial'
     */
    public static function activeForCategory(string $category, Carbon $date): Collection
    {
        return static::where('is_active', true)
            ->where('category', $category)
            ->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $date);
            })
            ->orderBy('min_units')
            ->get();
    }

    /** Backward-compat: returns all active tiers regardless of category */
    public static function activeForDate(Carbon $date): Collection
    {
        return static::where('is_active', true)
            ->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $date);
            })
            ->orderBy('category')
            ->orderBy('min_units')
            ->get();
    }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'commercial'  => 'Commercial',
            default       => 'Residential',
        };
    }
}
