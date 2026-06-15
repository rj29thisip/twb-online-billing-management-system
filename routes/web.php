<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboard;
use App\Http\Controllers\Customer\UsageController;
use App\Http\Controllers\Customer\HistoryController;
use App\Http\Controllers\Customer\InvoiceController as CustomerInvoice;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\MeterController;
use App\Http\Controllers\Admin\MeterReadingController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoice;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\TariffController;
use App\Http\Controllers\Admin\TaxController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DistrictController;
use App\Http\Controllers\Admin\EmailConfigController;
use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\Auth\ForcePasswordChangeController;

// ─── Root ──────────────────────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('login'));

// ─── Auth ──────────────────────────────────────────────────────────────────────
Route::get('/login',   [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login',  [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ─── Forgot / Reset Password (guest only) ─────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/admin/forgot-password',        [ForgotPasswordController::class, 'showLinkRequestForm'])->name('admin.password.request');
    Route::post('/admin/forgot-password',       [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('admin.password.email');
    Route::get('/admin/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('admin.password.reset');
    Route::post('/admin/reset-password',        [ForgotPasswordController::class, 'reset'])->name('admin.password.update');
});

// ─── Notifications (shared) ───────────────────────────────────────────────────
Route::get('/notifications', [NotificationController::class, 'index'])->middleware('auth')->name('notifications.index');

// ─── Customer Portal ──────────────────────────────────────────────────────────
Route::prefix('customer')->name('customer.')->middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/dashboard',               [CustomerDashboard::class, 'index'])->name('dashboard');
    Route::get('/usage',                   [UsageController::class,   'index'])->name('usage');
    Route::get('/usage/hourly',            [UsageController::class,   'hourly'])->name('usage.hourly');
    Route::get('/history',                 [HistoryController::class, 'index'])->name('history');
    Route::get('/invoices',                [CustomerInvoice::class,   'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}',      [CustomerInvoice::class,   'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/pdf',  [CustomerInvoice::class,   'pdf'])->name('invoices.pdf');
    Route::get('/profile',                 [CustomerDashboard::class, 'profile'])->name('profile');
    Route::put('/profile',                 [CustomerDashboard::class, 'updateProfile'])->name('profile.update');
    Route::get('/account/change-password', [\App\Http\Controllers\Customer\ChangePasswordController::class, 'show'])->name('account.password.show');
    Route::put('/account/change-password', [\App\Http\Controllers\Customer\ChangePasswordController::class, 'update'])->name('account.password.update');
});

// ─── Admin Panel ───────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,officer,cashier,account_employee,ceo,accountant,manager'])->group(function () {

    // Force password change — must be accessible before anything else
    Route::get('/change-password',  [ForcePasswordChangeController::class, 'showChangeForm'])->name('password.change.form');
    Route::post('/change-password', [ForcePasswordChangeController::class, 'update'])->name('password.change.update');

    // Dashboard + PDF export (all staff)
    Route::get('/dashboard',          [AdminDashboard::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export-pdf', [AdminDashboard::class, 'exportPdf'])->name('dashboard.export-pdf');

    // Customers (admin, cashier, account_employee)
    Route::middleware('permission:canManageCustomers')->group(function () {
        Route::resource('customers', CustomerController::class);
    });

    // Meters (admin, account_employee)
    Route::middleware('permission:canAccessMeters')->group(function () {
        Route::resource('meters', MeterController::class);
    });

    // Meter Readings (admin, account_employee)
    Route::middleware('permission:canManageMeterReadings')->group(function () {
        Route::get('/readings',             [MeterReadingController::class, 'index'])->name('readings.index');
        Route::get('/readings/import',      [MeterReadingController::class, 'importForm'])->name('readings.import');
        Route::post('/readings/import',     [MeterReadingController::class, 'import'])->name('readings.import.post');
        Route::post('/readings/manual',     [MeterReadingController::class, 'manual'])->name('readings.manual');
        Route::patch('/readings/{reading}/anomaly', [MeterReadingController::class, 'resolveAnomaly'])->name('readings.anomaly.resolve');
    });

    // Invoices list & detail (admin, cashier, account_employee)
    Route::middleware('permission:canViewInvoices')->group(function () {
        Route::get('/invoices',                      [AdminInvoice::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}',             [AdminInvoice::class, 'show'])->name('invoices.show');
        Route::delete('/invoices/{invoice}',          [AdminInvoice::class, 'destroy'])->name('invoices.destroy');
        Route::get('/invoices/{invoice}/pdf',         [AdminInvoice::class, 'pdf'])->name('invoices.pdf');
        Route::post('/invoices/{invoice}/send-email', [AdminInvoice::class, 'sendEmail'])->name('invoices.send-email');
    });

    // Create Invoices — billing check/generate (admin, account_employee only)
    Route::middleware('permission:canCreateInvoices')->group(function () {
        Route::get('/billing/check',    [AdminInvoice::class, 'checkBilling'])->name('billing.check');
        Route::post('/billing/generate',[AdminInvoice::class, 'generateBulk'])->name('billing.generate');
    });

    // Payments (admin, cashier)
    Route::middleware('permission:canProcessPayments')->group(function () {
        Route::get('/payments',                   [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments',                  [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/{payment}',         [PaymentController::class, 'show'])->name('payments.show');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    });

    // Configuration (admin, account_employee)
    Route::middleware('permission:canAccessConfig')->group(function () {
        Route::get('/config/tariffs',             [TariffController::class, 'index'])->name('config.tariffs.index');
        Route::post('/config/tariffs',            [TariffController::class, 'store'])->name('config.tariffs.store');
        Route::put('/config/tariffs/{tariff}',    [TariffController::class, 'update'])->name('config.tariffs.update');
        Route::delete('/config/tariffs/{tariff}', [TariffController::class, 'destroy'])->name('config.tariffs.destroy');

        Route::get('/config/taxes',           [TaxController::class, 'index'])->name('config.taxes.index');
        Route::post('/config/taxes',          [TaxController::class, 'store'])->name('config.taxes.store');
        Route::put('/config/taxes/{tax}',     [TaxController::class, 'update'])->name('config.taxes.update');
        Route::delete('/config/taxes/{tax}',  [TaxController::class, 'destroy'])->name('config.taxes.destroy');

        Route::get('/config/discounts',               [DiscountController::class, 'index'])->name('config.discounts.index');
        Route::post('/config/discounts',              [DiscountController::class, 'store'])->name('config.discounts.store');
        Route::put('/config/discounts/{discount}',    [DiscountController::class, 'update'])->name('config.discounts.update');
        Route::delete('/config/discounts/{discount}', [DiscountController::class, 'destroy'])->name('config.discounts.destroy');

        Route::resource('announcements', AnnouncementController::class);
    });

    // Users / Staff (admin only)
    Route::get('/users',           [UserController::class, 'index'])->name('users.index')->middleware('role:admin');
    Route::post('/users',          [UserController::class, 'store'])->name('users.store')->middleware('role:admin');
    Route::put('/users/{user}',    [UserController::class, 'update'])->name('users.update')->middleware('role:admin');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('role:admin');

    // Districts (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::resource('districts', DistrictController::class)->except(['destroy']);
        Route::patch('districts/{district}/toggle-active', [DistrictController::class, 'toggleActive'])->name('districts.toggle-active');
    });

    // Email Config (admin only)
    Route::middleware('role:admin')->prefix('email-config')->name('email-config.')->group(function () {
        Route::get('/',                    [EmailConfigController::class, 'index'])->name('index');
        Route::get('/create',              [EmailConfigController::class, 'create'])->name('create');
        Route::post('/',                   [EmailConfigController::class, 'store'])->name('store');
        Route::get('/{emailConfig}/edit',  [EmailConfigController::class, 'edit'])->name('edit');
        Route::put('/{emailConfig}',       [EmailConfigController::class, 'update'])->name('update');
        Route::delete('/{emailConfig}',    [EmailConfigController::class, 'destroy'])->name('destroy');
        Route::post('/{emailConfig}/test', [EmailConfigController::class, 'sendTest'])->name('test');
    });

    // Change Password (all authenticated staff)
    Route::get('/account/change-password',  [\App\Http\Controllers\Admin\ChangePasswordController::class, 'show'])->name('account.password.show');
    Route::put('/account/change-password',  [\App\Http\Controllers\Admin\ChangePasswordController::class, 'update'])->name('account.password.update');

    // Audit Logs
    Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');

    // API endpoints for searchable dropdowns (Select2 AJAX)
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/customers/search', function (\Illuminate\Http\Request $request) {
            $q = $request->get('q', '');
            $results = \App\Models\Customer::where(function($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")->orWhere('account_number', 'like', "%{$q}%");
                })->limit(20)->get(['id','name','account_number'])
                ->map(fn($c) => ['id' => $c->id, 'text' => "{$c->account_number} — {$c->name}"]);
            return response()->json(['results' => $results]);
        })->name('customers.search');

        Route::get('/districts/search', function (\Illuminate\Http\Request $request) {
            $q = $request->get('q', '');
            $results = \App\Models\District::where('is_active', true)
                ->where(fn($query) => $query->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%"))
                ->orderByDesc('is_headquarters')->orderBy('name')->limit(20)->get(['id','name','code','is_headquarters'])
                ->map(fn($d) => ['id' => $d->id, 'text' => $d->name.($d->is_headquarters ? ' (HQ)' : '')]);
            return response()->json(['results' => $results]);
        })->name('districts.search');

        Route::get('/meters/search', function (\Illuminate\Http\Request $request) {
            $q = $request->get('q', '');
            $customerId = $request->get('customer_id');
            $query = \App\Models\Meter::with('customer:id,name,account_number')
                ->where(fn($mq) => $mq->where('meter_id', 'like', "%{$q}%")
                    ->orWhereHas('customer', fn($cq) => $cq->where('name','like',"%{$q}%")->orWhere('account_number','like',"%{$q}%")));
            if ($customerId) $query->where('customer_id', $customerId);
            $results = $query->limit(20)->get()
                ->map(fn($m) => ['id' => $m->id, 'text' => "{$m->meter_id} — {$m->customer->account_number} ({$m->customer->name})"]);
            return response()->json(['results' => $results]);
        })->name('meters.search');
    });
});
