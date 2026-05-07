<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    protected $fillable = [
        'name',
        'rate_percent',
        'is_active',
        'effective_from',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'is_active'      => 'boolean',
        'rate_percent'   => 'decimal:2',
    ];
}
