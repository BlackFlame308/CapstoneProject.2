<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CSVUploadController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

// 🔽 Models for API dropdowns
use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;
use App\Models\Sitio;

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
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/analytics/update', [DashboardController::class, 'updateAnalytics'])->name('analytics.update');

    // Household CRUD
    Route::resource('households', HouseholdController::class);

    // CSV Upload
    Route::get('csv/upload', [CSVUploadController::class, 'uploadForm'])->name('csv.upload');
    Route::post('csv/upload', [CSVUploadController::class, 'upload'])->name('csv.upload.process');

    // Account management
    Route::get('accounts', [AccountController::class, 'index'])->name('accounts.index');

    /*
    |--------------------------------------------------------------------------
    | 🌍 LOCATION API (FOR DROPDOWNS)
    |--------------------------------------------------------------------------
    */

    // Get all regions
    Route::get('/api/regions', function () {
        return Region::all();
    });

    // Get provinces by region
    Route::get('/api/provinces/{region_id}', function ($region_id) {
        return Province::where('region_id', $region_id)->get();
    });

    // Get cities by province
    Route::get('/api/cities/{province_id}', function ($province_id) {
        return City::where('province_id', $province_id)->get();
    });

    // Get barangays by city
    Route::get('/api/barangays/{city_id}', function ($city_id) {
        return Barangay::where('city_id', $city_id)->get();
    });

    // Get sitios by barangay
    Route::get('/api/sitios/{barangay_id}', function ($barangay_id) {
        return Sitio::where('barangay_id', $barangay_id)->get();
    });
});

/*
|--------------------------------------------------------------------------
| Default Route
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});