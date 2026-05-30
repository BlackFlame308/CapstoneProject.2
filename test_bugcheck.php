<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Member;

// Check 1: Member vulnerableGroups relationship
$m = new Member();
$methods = get_class_methods($m);
$hasVG = in_array('vulnerableGroups', $methods);
echo "Member::vulnerableGroups relationship: " . ($hasVG ? 'EXISTS' : 'MISSING') . PHP_EOL;

// Check 2: ImportLog status column values in DB
$statuses = \Illuminate\Support\Facades\DB::table('import_logs')->select('status')->distinct()->get()->pluck('status');
echo "ImportLog statuses in DB: [" . $statuses->implode(', ') . "]" . PHP_EOL;

// Check 3: HouseholdPolicy
$policyExists = class_exists('App\Policies\HouseholdPolicy');
echo "HouseholdPolicy class: " . ($policyExists ? 'EXISTS' : 'MISSING') . PHP_EOL;

// Check 4: CsvUploadController authorize - what policy is registered?
$authManager = app(\Illuminate\Auth\Access\Gate::class);
$policiesField = new \ReflectionProperty($authManager, 'policies');
$policiesField->setAccessible(true);
$policies = $policiesField->getValue($authManager);
echo "Registered policies: " . PHP_EOL;
foreach ($policies as $model => $policy) {
    echo "  {$model} => {$policy}" . PHP_EOL;
}

// Check 5: User->getAgeAttribute - does Member have getAgeAttribute or full_name?
$memberRef = new \ReflectionClass(Member::class);
$hasFn = $memberRef->hasMethod('getFullNameAttribute') || $memberRef->hasMethod('full_name');
echo "Member has getFullNameAttribute: " . ($hasFn ? 'YES' : 'NO') . PHP_EOL;
// Check for attribute method or cast
$attrs = (new Member())->getFillable();
echo "Member fillable count: " . count($attrs) . PHP_EOL;

// Check 6: VulnerableGroupAdminController views exist?
$vgViews = [
    'resources/views/admin/vulnerable-groups/index.blade.php',
    'resources/views/admin/vulnerable-groups/create.blade.php',
    'resources/views/admin/vulnerable-groups/edit.blade.php',
];
foreach ($vgViews as $v) {
    echo "View {$v}: " . (file_exists($v) ? 'OK' : 'MISSING') . PHP_EOL;
}

// Check 7: CSV-import views
$csvViews = [
    'resources/views/admin/csv-import/dashboard.blade.php',
    'resources/views/admin/csv-import/show.blade.php',
];
foreach ($csvViews as $v) {
    echo "View {$v}: " . (file_exists($v) ? 'OK' : 'MISSING') . PHP_EOL;
}

// Check 8: search views
$searchViews = [
    'resources/views/admin/search/advanced-search.blade.php',
    'resources/views/admin/search/results.blade.php',
];
foreach ($searchViews as $v) {
    echo "View {$v}: " . (file_exists($v) ? 'OK' : 'MISSING') . PHP_EOL;
}

// Check 9: household show view uses correct address chain
$showBlade = file_get_contents('resources/views/admin/households/show.blade.php');
echo "show.blade uses barangay name: " . (str_contains($showBlade, 'barangay') ? 'YES' : 'NO') . PHP_EOL;
echo "show.blade loads address relationship: " . (str_contains($showBlade, 'address') ? 'YES' : 'NO') . PHP_EOL;

// Check 10: AdminDashboardController sends correct variables
$dashCode = file_get_contents('app/Http/Controllers/Admin/AdminDashboardController.php');
echo "Dashboard sends totalHouseholds: " . (str_contains($dashCode, 'totalHouseholds') ? 'YES' : 'NO') . PHP_EOL;
echo "Dashboard sends totalPopulation: " . (str_contains($dashCode, 'totalPopulation') ? 'YES' : 'NO') . PHP_EOL;
