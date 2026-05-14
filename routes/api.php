<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\HouseholdController;
use App\Http\Controllers\API\MemberController;

// Public routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Location hierarchy now served via web routes.
// Route::get('locations/regions', [...])
// Route::get('locations/{parentId}/children', [...])

// Protected routes
Route::middleware(['auth:sanctum', 'role:Captain|Encoder'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('change-password', [AuthController::class, 'changePassword']);

    Route::get('user', function (Request $request) {
        return response()->json(["status" => "success", "data" => $request->user()]);
    });

    // Household Management - Captain full access, Encoder can create/view (NO delete)
    Route::get('households', [HouseholdController::class, 'index'])->name('api.households.index');
    Route::post('households', [HouseholdController::class, 'store'])->name('api.households.store');
    Route::get('households/{household}', [HouseholdController::class, 'show'])->name('api.households.show');
    Route::put('households/{household}', [HouseholdController::class, 'update'])->name('api.households.update');
    // DELETE only for Captain
    Route::delete('households/{household}', [HouseholdController::class, 'destroy'])
        ->middleware('role:Captain');
    
    Route::post('households/upload-csv', [HouseholdController::class, 'uploadCsv'])
        ->middleware('role:Captain');

    // Member Management - Captain/Encoder can view, Captain can modify
    Route::get('members', [MemberController::class, 'index'])->name('api.members.index');
    Route::post('members', [MemberController::class, 'store'])->name('api.members.store');
    Route::get('members/{member}', [MemberController::class, 'show'])->name('api.members.show');
    Route::put('members/{member}', [MemberController::class, 'update'])->name('api.members.update');
    Route::delete('members/{member}', [MemberController::class, 'destroy'])
        ->middleware('role:Captain');
});
