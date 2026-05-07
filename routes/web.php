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
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuditLogController;

// ─── Root ────────────────────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('login'));

// ─── Auth ─────────────────────────────────────────────────────────────────────
Route::get('/login',   [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login',  [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ─── Notifications (customer bell icon) ───────────────────────────────────────
Route::get('/notifications', [NotificationController::class, 'index'])
    ->middleware('auth')
    ->name('notifications.index');

// ─── Customer Portal ─────────────────────────────────────────────────────────
Route::prefix('customer')->name('customer.')->middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/dashboard',              [CustomerDashboard::class, 'index'])->name('dashboard');
    Route::get('/usage',                  [UsageController::class,   'index'])->name('usage');
    Route::get('/usage/hourly',           [UsageController::class,   'hourly'])->name('usage.hourly');
    Route::get('/history',                [HistoryController::class, 'index'])->name('history');
    Route::get('/invoices',               [CustomerInvoice::class,   'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}',     [CustomerInvoice::class,   'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/pdf', [CustomerInvoice::class,   'pdf'])->name('invoices.pdf');
    Route::get('/profile',                [CustomerDashboard::class, 'profile'])->name('profile');
    Route::put('/profile',                [CustomerDashboard::class, 'updateProfile'])->name('profile.update');
});

// ─── Admin Panel ──────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,officer'])->group(function () {

    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Customers
    Route::resource('customers', CustomerController::class);

    // Meters
    Route::resource('meters', MeterController::class);

    // Meter Readings + XML/CSV Import
    Route::get('/readings',                             [MeterReadingController::class, 'index'])->name('readings.index');
    Route::get('/readings/import',                      [MeterReadingController::class, 'importForm'])->name('readings.import');
    Route::post('/readings/import',                     [MeterReadingController::class, 'import'])->name('readings.import.post');
    Route::post('/readings/manual',                     [MeterReadingController::class, 'manual'])->name('readings.manual');
    Route::patch('/readings/{reading}/anomaly',         [MeterReadingController::class, 'resolveAnomaly'])->name('readings.anomaly.resolve');

    // Users (admin only)
    Route::get('/users',           [UserController::class, 'index'])->name('users.index')->middleware('role:admin');
    Route::post('/users',          [UserController::class, 'store'])->name('users.store')->middleware('role:admin');
    Route::put('/users/{user}',    [UserController::class, 'update'])->name('users.update')->middleware('role:admin');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('role:admin');

    // Audit Logs
    Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');
});
