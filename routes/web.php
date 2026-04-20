<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CSVUploadController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// Registration for initial setup or captains managing accounts
Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('register', [RegisteredUserController::class, 'store']);

// Logout
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

// Auth required routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/analytics/update', [DashboardController::class, 'updateAnalytics'])->name('analytics.update');
    
    // Household management
    Route::resource('households', HouseholdController::class);

    // CSV Upload
    Route::get('csv/upload', [CSVUploadController::class, 'uploadForm'])->name('csv.upload');
    Route::post('csv/upload', [CSVUploadController::class, 'upload'])->name('csv.upload.process');

    // Account management
    Route::get('accounts', [AccountController::class, 'index'])->name('accounts.index');
});

Route::get('/', function () {
    return view('welcome');
});
