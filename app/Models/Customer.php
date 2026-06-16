<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    protected $fillable = [
        // Account
        'account_number', 'status', 'customer_type', 'district_id', 'block_number',
        // Personal
        'name', 'given_name', 'family_name', 'date_of_birth', 'gender',
        // Contact
        'email', 'phone',
        // Address
        'address', 'address_line', 'suburb', 'island', 'island_code',
        // Property
        'deed_number', 'surveyed_date', 'property_notes',
        // Tracking
        'created_by',
    ];

    protected $casts = [
        'date_of_birth'  => 'date',
        'surveyed_date'  => 'date',
    ];

    // ─── Helpers ───────────────────────────────────────────────────────────────

    /** Returns human-readable customer type label */
    public function getCustomerTypeLabelAttribute(): string
    {
        return match($this->customer_type) {
            'commercial' => 'Commercial',
            default      => 'Residential',
        };
    }

    /** True if commercial customer */
    public function isCommercial(): bool
    {
        return $this->customer_type === 'commercial';
    }

    /** Full name derived from given + family, or fallback to name */
    public function getFullNameAttribute(): string
    {
        if ($this->given_name || $this->family_name) {
            return trim(($this->given_name ?? '') . ' ' . ($this->family_name ?? ''));
        }
        return $this->name ?? '';
    }

    /** Island label with code */
    public function getIslandLabelAttribute(): string
    {
        if ($this->island && $this->island_code) {
            return "{$this->island} ({$this->island_code})";
        }
        return $this->island ?? '—';
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

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
