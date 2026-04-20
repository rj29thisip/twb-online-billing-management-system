<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboard;
use App\Http\Controllers\Customer\UsageController;
use App\Http\Controllers\Customer\HistoryController;

// ─── Root ─────────────────────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('login'));

// ─── Auth ─────────────────────────────────────────────────────────────────────
Route::get('/login',   [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login',  [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ─── Notifications (customer only) ────────────────────────────────────────────
Route::get('/notifications', [NotificationController::class, 'index'])
    ->middleware('auth')
    ->name('notifications.index');

// ─── Customer Portal ──────────────────────────────────────────────────────────
Route::prefix('customer')->name('customer.')->middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/dashboard',    [CustomerDashboard::class, 'index'])->name('dashboard');
    Route::get('/usage',        [UsageController::class,   'index'])->name('usage');
    Route::get('/usage/hourly', [UsageController::class,   'hourly'])->name('usage.hourly');
    Route::get('/history',      [HistoryController::class, 'index'])->name('history');
    Route::get('/profile',      [CustomerDashboard::class, 'profile'])->name('profile');
    Route::put('/profile',      [CustomerDashboard::class, 'updateProfile'])->name('profile.update');
});
