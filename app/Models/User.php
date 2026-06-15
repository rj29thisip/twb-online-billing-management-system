<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use Notifiable;

    // ─── Role constants ────────────────────────────────────────────
    const ROLE_ADMIN            = 'admin';
    const ROLE_OFFICER          = 'officer';
    const ROLE_CASHIER          = 'cashier';
    const ROLE_ACCOUNT_EMPLOYEE = 'account_employee';
    const ROLE_CEO              = 'ceo';
    const ROLE_ACCOUNTANT       = 'accountant';
    const ROLE_MANAGER          = 'manager';
    const ROLE_CUSTOMER         = 'customer';

    public static array $staffRoles = [
        self::ROLE_ADMIN            => 'Administrator',
        self::ROLE_CASHIER          => 'Cashier',
        self::ROLE_ACCOUNT_EMPLOYEE => 'Account Employee',
        self::ROLE_CEO              => 'CEO',
        self::ROLE_ACCOUNTANT       => 'Accountant',
        self::ROLE_MANAGER          => 'Manager',
    ];

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'customer_id', 'is_active',
        'district_id', 'must_change_password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at'    => 'datetime',
        'is_active'            => 'boolean',
        'must_change_password' => 'boolean',
        'password'             => 'hashed',
    ];

    // ─── Relationships ──────────────────────────────────────────────
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    // ─── Role helpers ───────────────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isOfficer(): bool
    {
        return $this->role === self::ROLE_OFFICER;
    }

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function isStaff(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN, self::ROLE_OFFICER, self::ROLE_CASHIER,
            self::ROLE_ACCOUNT_EMPLOYEE, self::ROLE_CEO,
            self::ROLE_ACCOUNTANT, self::ROLE_MANAGER,
        ]);
    }

    public function isHeadquarters(): bool
    {
        return $this->district && $this->district->is_headquarters;
    }

    public function getRoleLabelAttribute(): string
    {
        return self::$staffRoles[$this->role] ?? ucfirst($this->role);
    }

    // ─── Permission helpers ─────────────────────────────────────────

    /** Customers menu (Customers section) */
    public function canManageCustomers(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN, self::ROLE_CASHIER, self::ROLE_ACCOUNT_EMPLOYEE,
        ]);
    }

    /** Meters list & detail */
    public function canAccessMeters(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN, self::ROLE_ACCOUNT_EMPLOYEE,
        ]);
    }

    /** Meter Readings */
    public function canManageMeterReadings(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN, self::ROLE_ACCOUNT_EMPLOYEE,
        ]);
    }

    /** Invoices list & detail (Cashier sees but cannot create) */
    public function canViewInvoices(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN, self::ROLE_CASHIER, self::ROLE_ACCOUNT_EMPLOYEE,
        ]);
    }

    /** Create Invoices — billing check/generate */
    public function canCreateInvoices(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN, self::ROLE_ACCOUNT_EMPLOYEE,
        ]);
    }

    /** Payments */
    public function canProcessPayments(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN, self::ROLE_CASHIER,
        ]);
    }

    /** Configuration section (tariffs / taxes / discounts / announcements) */
    public function canAccessConfig(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN, self::ROLE_ACCOUNT_EMPLOYEE,
        ]);
    }

    /** Backward-compat alias */
    public function canManageInvoices(): bool
    {
        return $this->canViewInvoices();
    }

    /** CEO / Accountant / Manager — dashboard only */
    public function isDashboardOnly(): bool
    {
        return in_array($this->role, [
            self::ROLE_CEO, self::ROLE_ACCOUNTANT, self::ROLE_MANAGER,
        ]);
    }

    /**
     * Dashboard title suffix based on district scope.
     * HQ / Admin → "All Districts"
     * Area staff  → district name
     */
    public function dashboardDistrictLabel(): string
    {
        if ($this->isAdmin() || $this->isHeadquarters() || !$this->district_id) {
            return 'All Districts';
        }
        return optional($this->district)->name ?? 'Unknown District';
    }

    /**
     * Scoped customer query based on user's district.
     * HQ / Admin → all customers
     * Area staff → own district + customers with no district
     */
    public function scopedCustomerQuery()
    {
        $query = \App\Models\Customer::query();

        if ($this->isAdmin() || $this->isHeadquarters() || !$this->district_id) {
            return $query;
        }

        return $query->where(function ($q) {
            $q->where('district_id', $this->district_id)
              ->orWhereNull('district_id');
        });
    }
}
