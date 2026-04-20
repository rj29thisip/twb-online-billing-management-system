<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meter extends Model
{
    protected $fillable = [
        'customer_id',
        'meter_id',
        'endpoint_id',
        'meter_type',
        'installation_date',
        'last_maintenance_date',
        'status',
        'brand',
        'model',
    ];

    protected $casts = [
        'installation_date'     => 'date',
        'last_maintenance_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function readings(): HasMany
    {
        return $this->hasMany(MeterReading::class)->orderByDesc('capture_time');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function lastReading(): ?MeterReading
    {
        return $this->readings()->latest('capture_time')->first();
    }

    public function currentMonthUsage(): float
    {
        return (float) $this->readings()
            ->whereYear('capture_time', now()->year)
            ->whereMonth('capture_time', now()->month)
            ->sum('usage');
    }
}
