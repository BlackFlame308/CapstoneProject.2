<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CsvUploadController;
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
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Dashboard - Captain can view, Encoder can view
    Route::get('/dashboard', function () {
         return redirect()->route('admin.dashboard');
    })->middleware('auth')->name('dashboard');

    Route::post('/analytics/update', [DashboardController::class, 'updateAnalytics'])
        ->name('analytics.update')
        ->middleware('role:Captain');


    // Household CRUD - Captain full access, Encoder can create/view, Household can view own
    Route::resource('households', HouseholdController::class)
        ->middleware('role:Captain|Encoder');


    // CSV Upload - Captain only
    Route::get('csv/upload', [CsvUploadController::class, 'uploadForm'])
        ->name('csv.upload');

    Route::post('csv/upload', [CsvUploadController::class, 'upload'])
        ->name('csv.upload.process');


    // Account management - Captain only
    Route::get('accounts', [AccountController::class, 'index'])
        ->name('accounts.index')
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
| ADMIN DASHBOARD ROUTES (Blade Views with Bootstrap)
|--------------------------------------------------------------------------
| All routes under /admin prefix
| Access: Barangay Head (role='head') or Encoder (role='encoder')
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');

    
    // Household Management
    Route::get('/households', [App\Http\Controllers\Admin\HouseholdAdminController::class, 'index'])->name('households.index');
    Route::get('/households/create', [App\Http\Controllers\Admin\HouseholdAdminController::class, 'create'])->name('households.create');
    Route::post('/households', [App\Http\Controllers\Admin\HouseholdAdminController::class, 'store'])->name('households.store');
    Route::get('/households/{household}', [App\Http\Controllers\Admin\HouseholdAdminController::class, 'show'])->name('households.show');
    Route::get('/households/{household}/edit', [App\Http\Controllers\Admin\HouseholdAdminController::class, 'edit'])->name('households.edit');
    Route::put('/households/{household}', [App\Http\Controllers\Admin\HouseholdAdminController::class, 'update'])->name('households.update');
    Route::delete('/households/{household}', [App\Http\Controllers\Admin\HouseholdAdminController::class, 'destroy'])
        ->middleware('can:delete,household')->name('households.destroy');
    Route::post('/households/{household}/csv-upload', [App\Http\Controllers\Admin\HouseholdAdminController::class, 'uploadCsv'])->name('households.csv-upload');
    
    // Resident/Member Management
    Route::get('/residents', [App\Http\Controllers\Admin\ResidentAdminController::class, 'index'])->name('residents.index');
    Route::get('/residents/{household}/create', [App\Http\Controllers\Admin\ResidentAdminController::class, 'create'])->name('residents.create');
    Route::post('/residents/{household}', [App\Http\Controllers\Admin\ResidentAdminController::class, 'store'])->name('residents.store');
    Route::get('/residents/{member}/edit', [App\Http\Controllers\Admin\ResidentAdminController::class, 'edit'])->name('residents.edit');
    Route::put('/residents/{member}', [App\Http\Controllers\Admin\ResidentAdminController::class, 'update'])->name('residents.update');
    Route::delete('/residents/{member}', [App\Http\Controllers\Admin\ResidentAdminController::class, 'destroy'])
        ->middleware('can:delete,member')->name('residents.destroy');
    
    // Account Management
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
    
    // API Tokens
    Route::get('/tokens', [App\Http\Controllers\TokenController::class, 'index'])->name('tokens.index');
    Route::get('/reports/evacuation', [App\Http\Controllers\Admin\ReportAdminController::class, 'evacuation'])->name('reports.evacuation');
    Route::get('/reports/rescue', [App\Http\Controllers\Admin\ReportAdminController::class, 'rescue'])->name('reports.rescue');
    Route::get('/reports/logistics', [App\Http\Controllers\Admin\ReportAdminController::class, 'logistics'])->name('reports.logistics');
    
    // Vulnerable Groups Management
    Route::get('/vulnerable-groups', [App\Http\Controllers\Admin\VulnerableGroupAdminController::class, 'index'])->name('vulnerable-groups.index');
    Route::get('/vulnerable-groups/create', [App\Http\Controllers\Admin\VulnerableGroupAdminController::class, 'create'])->name('vulnerable-groups.create');
    Route::post('/vulnerable-groups', [App\Http\Controllers\Admin\VulnerableGroupAdminController::class, 'store'])->name('vulnerable-groups.store');
    Route::get('/vulnerable-groups/{vulnerableGroup}/edit', [App\Http\Controllers\Admin\VulnerableGroupAdminController::class, 'edit'])->name('vulnerable-groups.edit');
    Route::put('/vulnerable-groups/{vulnerableGroup}', [App\Http\Controllers\Admin\VulnerableGroupAdminController::class, 'update'])->name('vulnerable-groups.update');
    Route::delete('/vulnerable-groups/{vulnerableGroup}', [App\Http\Controllers\Admin\VulnerableGroupAdminController::class, 'destroy'])->name('vulnerable-groups.destroy');
    
    // Device Token Tracking
    Route::get('/device-tokens', [App\Http\Controllers\Admin\DeviceTokenAdminController::class, 'index'])->name('device-tokens.index');
    Route::get('/device-tokens/{deviceToken}', [App\Http\Controllers\Admin\DeviceTokenAdminController::class, 'show'])->name('device-tokens.show');
    Route::delete('/device-tokens/{deviceToken}', [App\Http\Controllers\Admin\DeviceTokenAdminController::class, 'destroy'])->name('device-tokens.destroy');
    Route::get('/device-tokens/export/data', [App\Http\Controllers\Admin\DeviceTokenAdminController::class, 'export'])->name('device-tokens.export');
    
    // Advanced Search
    Route::get('/search', [App\Http\Controllers\Admin\AdvancedSearchController::class, 'form'])->name('search.form');
    Route::get('/search/results', [App\Http\Controllers\Admin\AdvancedSearchController::class, 'search'])->name('search.search');
    
    // Data Export
    Route::get('/export/households/excel', [App\Http\Controllers\Admin\DataExportController::class, 'exportHouseholdsExcel'])->name('export.households-excel');
    Route::get('/export/households/pdf', [App\Http\Controllers\Admin\DataExportController::class, 'exportHouseholdsPDF'])->name('export.households-pdf');
    Route::get('/export/members/excel', [App\Http\Controllers\Admin\DataExportController::class, 'exportMembersExcel'])->name('export.members-excel');
    Route::get('/export/members/pdf', [App\Http\Controllers\Admin\DataExportController::class, 'exportMembersPDF'])->name('export.members-pdf');
    Route::get('/export/analytics', [App\Http\Controllers\Admin\DataExportController::class, 'exportAnalyticsReport'])->name('export.analytics-report');
    
    // Audit Logs
    Route::get('/audit-logs', [App\Http\Controllers\Admin\AuditLogAdminController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{id}', [App\Http\Controllers\Admin\AuditLogAdminController::class, 'show'])->name('audit-logs.show');
    Route::post('/audit-logs/clear', [App\Http\Controllers\Admin\AuditLogAdminController::class, 'clearOldLogs'])->name('audit-logs.clear');
    
    // CSV Import Dashboard
    Route::get('/csv-import', [App\Http\Controllers\Admin\CSVImportDashboardController::class, 'index'])->name('csv-import.index');
    Route::get('/csv-import/{csvUpload}', [App\Http\Controllers\Admin\CSVImportDashboardController::class, 'show'])->name('csv-import.show');
    Route::post('/csv-import/{csvUpload}/retry', [App\Http\Controllers\Admin\CSVImportDashboardController::class, 'retryErrors'])->name('csv-import.retry');
    Route::delete('/csv-import/{csvUpload}', [App\Http\Controllers\Admin\CSVImportDashboardController::class, 'destroy'])->name('csv-import.destroy');
    
    // Notifications Management
    Route::get('/notifications', [App\Http\Controllers\Admin\NotificationManagementController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/create', [App\Http\Controllers\Admin\NotificationManagementController::class, 'create'])->name('notifications.create');
    Route::post('/notifications', [App\Http\Controllers\Admin\NotificationManagementController::class, 'store'])->name('notifications.store');
    Route::get('/notifications/{notification}', [App\Http\Controllers\Admin\NotificationManagementController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/{notification}/retry', [App\Http\Controllers\Admin\NotificationManagementController::class, 'retry'])->name('notifications.retry');
    Route::delete('/notifications/{notification}', [App\Http\Controllers\Admin\NotificationManagementController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/test', [App\Http\Controllers\Admin\NotificationManagementController::class, 'sendTest'])->name('notifications.test');
});

/*
|--------------------------------------------------------------------------
| Default Route — redirect to dashboard if authenticated
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});
