<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CSVUploadController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordController;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Registration
|--------------------------------------------------------------------------
*/
Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('register', [RegisteredUserController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Password Change (must be accessible before full auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('password/change', [PasswordController::class, 'create'])->name('password.change')
        ->middleware('auth'); // Additional check for must_change_password could be added here
    Route::post('password/change', [PasswordController::class, 'update'])->name('password.update')
        ->middleware('auth');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES (Inertia + React)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Dashboard - Captain can view, Encoder can view
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')
        ->middleware('role:Captain|Encoder|Household');
    Route::post('/analytics/update', [DashboardController::class, 'updateAnalytics'])->name('analytics.update')
        ->middleware('role:Captain');

    // Household CRUD - Captain full access, Encoder can create/view, Household can view own
    Route::resource('households', HouseholdController::class)
        ->middleware('role:Captain|Encoder');

    // CSV Upload - Captain only
    Route::get('csv/upload', [CSVUploadController::class, 'uploadForm'])->name('csv.upload')
        ->middleware('role:Captain');
    Route::post('csv/upload', [CSVUploadController::class, 'upload'])->name('csv.upload.process')
        ->middleware('role:Captain');

    // Account management - Captain only
    Route::get('accounts', [AccountController::class, 'index'])->name('accounts.index')
        ->middleware('role:Captain');

    /*
    |--------------------------------------------------------------------------
    | LOCATION DROPDOWN ROUTES (internal — no public API)
    |--------------------------------------------------------------------------
    */
    Route::get('/locations/regions', [LocationController::class, 'regions'])->name('locations.regions')
        ->middleware('role:Captain|Encoder|Household');
    Route::get('/locations/provinces/{regionId}', [LocationController::class, 'provinces'])->name('locations.provinces')
        ->middleware('role:Captain|Encoder|Household');
    Route::get('/locations/cities/{provinceId}', [LocationController::class, 'cities'])->name('locations.cities')
        ->middleware('role:Captain|Encoder|Household');
    Route::get('/locations/barangays/{cityId}', [LocationController::class, 'barangays'])->name('locations.barangays')
        ->middleware('role:Captain|Encoder|Household');
    Route::get('/locations/sitios/{barangayId}', [LocationController::class, 'sitios'])->name('locations.sitios')
        ->middleware('role:Captain|Encoder|Household');
});

/*
|--------------------------------------------------------------------------
| Default Route — redirect to dashboard if authenticated
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

