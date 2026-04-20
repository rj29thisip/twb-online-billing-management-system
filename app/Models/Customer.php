<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    protected $fillable = [
        'account_number',
        'name',
        'phone',
        'email',
        'address',
        'block_number',
        'status',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function meters(): HasMany
    {
        return $this->hasMany(Meter::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->orderByDesc('billing_period_start');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function activeMeter(): HasOne
    {
        return $this->hasOne(Meter::class)->where('status', 'active')->latestOfMany();
    }

    public function outstandingBalance(): float
    {
        return (float) $this->invoices()
            ->whereIn('status', ['issued', 'partially_paid', 'overdue'])
            ->sum('balance_due');
    }
}
