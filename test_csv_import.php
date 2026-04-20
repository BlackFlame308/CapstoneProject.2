<?php

require_once 'vendor/autoload.php';

use App\Services\HouseholdCsvImportService;

try {
    $service = new HouseholdCsvImportService();
    $result = $service->import('test_households.csv', 1);
    echo json_encode($result, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}