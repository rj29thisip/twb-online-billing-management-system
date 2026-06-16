{{--
    resources/views/admin/partials/sidebar-additions.blade.php
    ──────────────────────────────────────────────────────────
    Merge these items into your existing sidebar navigation partial.
    Each block shows WHERE it belongs contextually.

    Assumes your sidebar uses:
      - auth()->user() to check the logged-in user
      - Route::has() or @can for conditional rendering
      - Bootstrap collapse or similar for grouped menus
--}}


{{-- ══════════════════════════════════════════════════════════════════════════
     CUSTOMERS MENU ITEM — show only to: administrator, cashier, account_employee
     Replace or conditionally wrap your existing Customers menu link.
     ══════════════════════════════════════════════════════════════════════════ --}}
@if (auth()->user()->canManageCustomers())
<li class="nav-item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
    <a href="{{ route('admin.customers.index') }}" class="nav-link">
        <i class="fas fa-users"></i>
        <span>Customers</span>
    </a>
</li>
@endif


{{-- ══════════════════════════════════════════════════════════════════════════
     METER READINGS — administrator, account_employee only
     ══════════════════════════════════════════════════════════════════════════ --}}
@if (auth()->user()->canManageMeterReadings())
<li class="nav-item {{ request()->routeIs('admin.meter-readings.*') ? 'active' : '' }}">
    <a href="{{ route('admin.meter-readings.index') }}" class="nav-link">
        <i class="fas fa-tachometer-alt"></i>
        <span>Meter Readings</span>
    </a>
</li>
@endif


{{-- ══════════════════════════════════════════════════════════════════════════
     INVOICES — administrator, account_employee only
     ══════════════════════════════════════════════════════════════════════════ --}}
@if (auth()->user()->canManageInvoices())
<li class="nav-item {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
    <a href="{{ route('admin.invoices.index') }}" class="nav-link">
        <i class="fas fa-file-invoice"></i>
        <span>Invoices</span>
    </a>
</li>
@endif


{{-- ══════════════════════════════════════════════════════════════════════════
     PAYMENTS — administrator, cashier only
     ══════════════════════════════════════════════════════════════════════════ --}}
@if (auth()->user()->canProcessPayments())
<li class="nav-item {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
    <a href="{{ route('admin.payments.index') }}" class="nav-link">
        <i class="fas fa-credit-card"></i>
        <span>Payments</span>
    </a>
</li>
@endif


{{-- ══════════════════════════════════════════════════════════════════════════
     SETTINGS GROUP — Administrator only
     Add Districts and Email Config under your existing Settings section.
     ══════════════════════════════════════════════════════════════════════════ --}}
@if (auth()->user()->isAdministrator())

{{-- Districts --}}
<li class="nav-item {{ request()->routeIs('admin.districts.*') ? 'active' : '' }}">
    <a href="{{ route('admin.districts.index') }}" class="nav-link">
        <i class="fas fa-map-marker-alt"></i>
        <span>Districts</span>
    </a>
</li>

{{-- Email Configuration --}}
<li class="nav-item {{ request()->routeIs('admin.email-config.*') ? 'active' : '' }}">
    <a href="{{ route('admin.email-config.index') }}" class="nav-link">
        <i class="fas fa-envelope-open-text"></i>
        <span>Email Config</span>
    </a>
</li>

@endif


{{-- ══════════════════════════════════════════════════════════════════════════
     DISTRICT BADGE — Show logged-in staff's district in the sidebar header/footer
     Place near the user's name/avatar section in the sidebar.
     ══════════════════════════════════════════════════════════════════════════ --}}
@if (auth()->user()->district)
<div class="sidebar-district-badge">
    <i class="fas fa-map-pin me-1"></i>
    {{ auth()->user()->district->name }}
    @if (auth()->user()->district->is_headquarters)
        <span class="badge bg-primary ms-1" style="font-size:10px;">HQ</span>
    @endif
</div>
@endif
