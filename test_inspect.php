<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== INSERTION TEST ===" . PHP_EOL;
try {
    $addr = App\Models\Address::create([
        'barangay_id' => 1,
        'purok_sitio' => 'Purok Test',
        'street' => 'Test St'
    ]);
    echo "Created Address ID: " . $addr->address_id . PHP_EOL;
    echo "Address Attributes: " . PHP_EOL;
    print_r($addr->getAttributes());

    $hCode = 'SYSTEST-HH-001';
    $household = App\Models\Household::create([
        'household_code' => $hCode,
        'household_name' => 'System Test Family',
        'address_id' => $addr->address_id,
        'created_by' => 'SUP-2026-QWBC8W',
        'contact_number' => '09171234567',
        'email' => null,
    ]);
    echo "Created Household ID: " . $household->household_id . PHP_EOL;
    echo "Household Attributes: " . PHP_EOL;
    print_r($household->getAttributes());
    echo "Household->address relation: " . PHP_EOL;
    print_r($household->address);

    // Clean up
    $household->forceDelete();
    $addr->forceDelete();
} catch (\Throwable $e) {
    echo "Insertion test failed: " . $e->getMessage() . PHP_EOL;
}

echo "=== MEMBER TEST ===" . PHP_EOL;
try {
    $addr = App\Models\Address::create([
        'barangay_id' => 1,
        'purok_sitio' => 'Purok Test',
        'street' => 'Test St'
    ]);
    $hCode = 'SYSTEST-HH-001';
    $household = App\Models\Household::create([
        'household_code' => $hCode,
        'household_name' => 'System Test Family',
        'address_id' => $addr->address_id,
        'created_by' => 'SUP-2026-QWBC8W',
        'contact_number' => '09171234567',
        'email' => null,
    ]);

    $child = App\Models\Member::create([
        'member_id' => (string)Illuminate\Support\Str::uuid(),
        'household_id' => $household->household_id,
        'first_name' => 'Maria',
        'last_name' => 'Cruz',
        'name' => 'Maria Cruz',
        'birth_date' => '2015-03-10',
        'sex' => 'F',
        'gender' => 'Female',
        'age' => 10,
        'relation' => 'Child',
        'civil_status' => 'Single',
        'is_pwd' => false,
        'is_pregnant' => false,
        'is_senior' => false,
        'is_graduate' => false,
    ]);

    echo "Child ID: " . $child->member_id . PHP_EOL;
    echo "Is in DB before delete: " . (App\Models\Member::find($child->member_id) !== null ? 'YES' : 'NO') . PHP_EOL;

    $child->delete();
    echo "Is in DB after delete (normal query): " . (App\Models\Member::find($child->member_id) !== null ? 'YES' : 'NO') . PHP_EOL;
    echo "Is in DB after delete (withTrashed query): " . (App\Models\Member::withTrashed()->find($child->member_id) !== null ? 'YES' : 'NO') . PHP_EOL;

    // Clean up
    $household->forceDelete();
    $addr->forceDelete();
} catch (\Throwable $e) {
    echo "Member test failed: " . $e->getMessage() . PHP_EOL;
}

echo "=== HOUSEHOLDS SCHEMA ===" . PHP_EOL;
try {
    $first = DB::table('households')->first();
    echo "First Household record:" . PHP_EOL;
    print_r($first);
} catch (\Throwable $e) {
    echo "Error reading households: " . $e->getMessage() . PHP_EOL;
}
