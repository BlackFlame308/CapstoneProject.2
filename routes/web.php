<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordController;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// Logout
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Password Change
Route::middleware('auth')->group(function () {
    Route::get('password/change', [PasswordController::class, 'create'])->name('password.change');
    Route::post('password/change', [PasswordController::class, 'update'])->name('password.update');
});

// Dashboard redirect
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware('auth')->name('dashboard');

// Admin Routes
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])
        ->name('dashboard');

    // Households
    Route::get('/households', [App\Http\Controllers\Admin\HouseholdAdminController::class, 'index'])->name('households.index');
    Route::get('/households/create', [App\Http\Controllers\Admin\HouseholdAdminController::class, 'create'])->name('households.create');
    Route::post('/households', [App\Http\Controllers\Admin\HouseholdAdminController::class, 'store'])->name('households.store');
    Route::get('/households/{household}', [App\Http\Controllers\Admin\HouseholdAdminController::class, 'show'])->name('households.show');
    Route::get('/households/{household}/edit', [App\Http\Controllers\Admin\HouseholdAdminController::class, 'edit'])->name('households.edit');
    Route::put('/households/{household}', [App\Http\Controllers\Admin\HouseholdAdminController::class, 'update'])->name('households.update');
    Route::delete('/households/{household}', [App\Http\Controllers\Admin\HouseholdAdminController::class, 'destroy'])->name('households.destroy');

    // Residents
    Route::get('/residents', [App\Http\Controllers\Admin\ResidentAdminController::class, 'index'])->name('residents.index');
    Route::get('/residents/{household}/create', [App\Http\Controllers\Admin\ResidentAdminController::class, 'create'])->name('residents.create');
    Route::post('/residents/{household}', [App\Http\Controllers\Admin\ResidentAdminController::class, 'store'])->name('residents.store');
    Route::get('/residents/{member}/edit', [App\Http\Controllers\Admin\ResidentAdminController::class, 'edit'])->name('residents.edit');
    Route::put('/residents/{member}', [App\Http\Controllers\Admin\ResidentAdminController::class, 'update'])->name('residents.update');
    Route::delete('/residents/{member}', [App\Http\Controllers\Admin\ResidentAdminController::class, 'destroy'])->name('residents.destroy');

    // Accounts
    Route::get('/accounts', [App\Http\Controllers\Admin\AccountAdminController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/create', [App\Http\Controllers\Admin\AccountAdminController::class, 'create'])->name('accounts.create');
    Route::post('/accounts', [App\Http\Controllers\Admin\AccountAdminController::class, 'store'])->name('accounts.store');
    Route::get('/accounts/{user}/edit', [App\Http\Controllers\Admin\AccountAdminController::class, 'edit'])->name('accounts.edit');
    Route::put('/accounts/{user}', [App\Http\Controllers\Admin\AccountAdminController::class, 'update'])->name('accounts.update');
    Route::delete('/accounts/{user}', [App\Http\Controllers\Admin\AccountAdminController::class, 'destroy'])->name('accounts.destroy');

    // Analytics
    Route::get('/analytics', [App\Http\Controllers\Admin\AnalyticsAdminController::class, 'index'])->name('analytics.index');

    // Reports
    Route::get('/reports', [App\Http\Controllers\Admin\ReportAdminController::class, 'index'])->name('reports.index');
    Route::get('/reports/evacuation', [App\Http\Controllers\Admin\ReportAdminController::class, 'evacuation'])->name('reports.evacuation');
    Route::get('/reports/rescue', [App\Http\Controllers\Admin\ReportAdminController::class, 'rescue'])->name('reports.rescue');
    Route::get('/reports/logistics', [App\Http\Controllers\Admin\ReportAdminController::class, 'logistics'])->name('reports.logistics');

    // Tokens
    Route::get('/tokens', [App\Http\Controllers\TokenController::class, 'index'])->name('tokens.index');

    // CSV Upload
    Route::get('/csv/upload', [App\Http\Controllers\CSVUploadController::class, 'uploadForm'])->name('csv.upload');
    Route::post('/csv/upload', [App\Http\Controllers\CSVUploadController::class, 'upload'])->name('csv.upload.process');
});

// Default
Route::get('/', function () {
    return redirect()->route('login');
});