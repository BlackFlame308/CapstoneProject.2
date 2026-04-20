<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\HouseholdController;
use App\Http\Controllers\API\MemberController;
use App\Http\Controllers\API\AnalyticController;
use App\Http\Controllers\API\RegionController;
use App\Http\Controllers\API\ProvinceController;
use App\Http\Controllers\API\CityController;

// Public routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Location hierarchy (public for dropdowns)
Route::get('regions', [RegionController::class, 'index']);
Route::get('regions/{region}/provinces', [RegionController::class, 'provinces']);
Route::get('provinces/{province}/cities', [ProvinceController::class, 'cities']);
Route::get('cities/{city}/barangays', [CityController::class, 'barangays']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('change-password', [AuthController::class, 'changePassword']);

    Route::get('user', function (Request $request) {
        return response()->json(["status" => "success", "data" => $request->user()]);
    });

    // Household Management
    Route::apiResource('households', HouseholdController::class)->names([
        'index' => 'api.households.index',
        'store' => 'api.households.store',
        'show' => 'api.households.show',
        'update' => 'api.households.update',
        'destroy' => 'api.households.destroy'
    ]);
    Route::post('households/upload-csv', [HouseholdController::class, 'uploadCsv']);

    // Member Management
    Route::apiResource('members', MemberController::class)->names([
        'index' => 'api.members.index',
        'store' => 'api.members.store',
        'show' => 'api.members.show',
        'update' => 'api.members.update',
        'destroy' => 'api.members.destroy'
    ]);

    // Analytics
    Route::get('analytics/barangay', [AnalyticController::class, 'barangay']);
    Route::get('analytics/sitio', [AnalyticController::class, 'sitio']);
    Route::post('analytics/refresh', [AnalyticController::class, 'refresh']);
});
