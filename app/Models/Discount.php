<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'name',
        'discount_type',
        'value',
        'applies_to',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'value'     => 'decimal:4',
    ];
}
