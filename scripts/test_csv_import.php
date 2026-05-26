<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\HouseholdCsvImportService;
use App\Models\User;

try {
    // Get the first user (captain or encoder) as uploaded_by
    $user = User::first();
    if (!$user) {
        throw new Exception('No users found in database. Run seeder first.');
    }
    
    $service = new HouseholdCsvImportService();
    $result = $service->import('test_households.csv', $user->id);
    echo json_encode($result, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
