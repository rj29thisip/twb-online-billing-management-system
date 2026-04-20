<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterReading extends Model
{
    protected $fillable = [
        'meter_id',
        'capture_time',
        'received_time',
        'register_type',
        'value',
        'usage',
        'source',
        'is_anomaly',
        'anomaly_note',
    ];

    protected $casts = [
        'capture_time'  => 'datetime',
        'received_time' => 'datetime',
        'value'         => 'decimal:4',
        'usage'         => 'decimal:4',
        'is_anomaly'    => 'boolean',
    ];

    public function meter(): BelongsTo
    {
        return $this->belongsTo(Meter::class);
    }
}
