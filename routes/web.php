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

// ─── Root ─────────────────────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('login'));

// ─── Auth ─────────────────────────────────────────────────────────────────────
Route::get('/login',   [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login',  [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ─── Notifications (shared — works for all authenticated roles) ───────────────
Route::get('/notifications', [NotificationController::class, 'index'])
    ->middleware('auth')
    ->name('notifications.index');

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
});

// ─── Admin Panel ──────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,officer'])->group(function () {

    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Customers
    Route::resource('customers', CustomerController::class);

    // Meters
    Route::resource('meters', MeterController::class);

    // Meter Readings
    Route::get('/readings',              [MeterReadingController::class, 'index'])->name('readings.index');
    Route::get('/readings/import',       [MeterReadingController::class, 'importForm'])->name('readings.import');
    Route::post('/readings/import',      [MeterReadingController::class, 'import'])->name('readings.import.post');
    Route::post('/readings/manual',      [MeterReadingController::class, 'manual'])->name('readings.manual');
    Route::patch('/readings/{reading}/anomaly', [MeterReadingController::class, 'resolveAnomaly'])->name('readings.anomaly.resolve');

    // Invoices
    Route::get('/invoices',                     [AdminInvoice::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}',            [AdminInvoice::class, 'show'])->name('invoices.show');
    Route::delete('/invoices/{invoice}',         [AdminInvoice::class, 'destroy'])->name('invoices.destroy');
    Route::get('/invoices/{invoice}/pdf',        [AdminInvoice::class, 'pdf'])->name('invoices.pdf');
    Route::post('/invoices/{invoice}/send-email',[AdminInvoice::class, 'sendEmail'])->name('invoices.send-email');
    Route::get('/billing/check',                 [AdminInvoice::class, 'checkBilling'])->name('billing.check');
    Route::post('/billing/generate',             [AdminInvoice::class, 'generateBulk'])->name('billing.generate');

    // Payments
    Route::get('/payments',                    [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments',                   [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}',          [PaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/{payment}/receipt',  [PaymentController::class, 'receipt'])->name('payments.receipt');

    // Configuration — Tariffs
    Route::get('/config/tariffs',              [TariffController::class, 'index'])->name('config.tariffs.index');
    Route::post('/config/tariffs',             [TariffController::class, 'store'])->name('config.tariffs.store');
    Route::put('/config/tariffs/{tariff}',     [TariffController::class, 'update'])->name('config.tariffs.update');
    Route::delete('/config/tariffs/{tariff}',  [TariffController::class, 'destroy'])->name('config.tariffs.destroy');

    // Configuration — Taxes
    Route::get('/config/taxes',            [TaxController::class, 'index'])->name('config.taxes.index');
    Route::post('/config/taxes',           [TaxController::class, 'store'])->name('config.taxes.store');
    Route::put('/config/taxes/{tax}',      [TaxController::class, 'update'])->name('config.taxes.update');
    Route::delete('/config/taxes/{tax}',   [TaxController::class, 'destroy'])->name('config.taxes.destroy');

    // Configuration — Discounts
    Route::get('/config/discounts',                [DiscountController::class, 'index'])->name('config.discounts.index');
    Route::post('/config/discounts',               [DiscountController::class, 'store'])->name('config.discounts.store');
    Route::put('/config/discounts/{discount}',     [DiscountController::class, 'update'])->name('config.discounts.update');
    Route::delete('/config/discounts/{discount}',  [DiscountController::class, 'destroy'])->name('config.discounts.destroy');

    // Announcements
    Route::resource('announcements', AnnouncementController::class);

    // Users (admin only)
    Route::get('/users',           [UserController::class, 'index'])->name('users.index')->middleware('role:admin');
    Route::post('/users',          [UserController::class, 'store'])->name('users.store')->middleware('role:admin');
    Route::put('/users/{user}',    [UserController::class, 'update'])->name('users.update')->middleware('role:admin');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('role:admin');

    // Audit Logs
    Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');
});
