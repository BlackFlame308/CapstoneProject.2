<?php
/**
 * REPAIR ADDRESSES SCRIPT
 * Restores missing address records for seeded or CSV-imported households
 * whose addresses were deleted by previous test cleanups.
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\{Household, Address, Barangay};
use Illuminate\Support\Facades\DB;

$csvPath = __DIR__ . '/../sample_household_test.csv';
if (!file_exists($csvPath)) {
    die("CSV file not found at: {$csvPath}\n");
}

echo "Starting address database repair...\n";

$file = fopen($csvPath, 'r');
$headers = fgetcsv($file);

// Map headers to indices
$headerMap = array_flip($headers);
$recreatedCount = 0;
$linkedCount = 0;

while (($row = fgetcsv($file)) !== false) {
    if (empty($row)) continue;
    
    $code = $row[$headerMap['household_code']] ?? null;
    if (empty($code)) continue;

    $hh = Household::where('household_code', $code)->first();
    if (!$hh) {
        echo "Household {$code} not found in database. Skipping.\n";
        continue;
    }

    $street = $row[$headerMap['street']] ?? '';
    $purok = $row[$headerMap['purok']] ?? '';
    $barangayName = $row[$headerMap['barangay']] ?? '';

    // Find the correct barangay
    $barangay = Barangay::where('name', $barangayName)->first();

    if ($hh->address_id) {
        // Address ID is recorded in household, check if it exists in addresses table
        $addrExists = DB::table('addresses')->where('address_id', $hh->address_id)->exists();
        if (!$addrExists) {
            // Recreate the missing Address record
            DB::table('addresses')->insert([
                'address_id' => $hh->address_id,
                'street' => $street,
                'street_address' => is_numeric($street) ? (int)$street : null,
                'purok_sitio' => $purok,
                'barangay_id' => $barangay ? $barangay->barangay_id : null,
                'barangay_name' => $barangayName,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "Recreated missing Address ID {$hh->address_id} for Household {$code} ({$street}, {$purok})\n";
            $recreatedCount++;
        } else {
            // Update physical columns in case they are null
            DB::table('addresses')
                ->where('address_id', $hh->address_id)
                ->update([
                    'street' => $street,
                    'purok_sitio' => $purok,
                    'barangay_id' => $barangay ? $barangay->barangay_id : null,
                    'barangay_name' => $barangayName,
                ]);
            echo "Updated existing Address ID {$hh->address_id} for Household {$code}\n";
        }
    } else {
        // Create new address record and link it
        $address = Address::create([
            'street' => $street,
            'purok_sitio' => $purok,
            'barangay_id' => $barangay ? $barangay->barangay_id : null,
            'barangay_name' => $barangayName
        ]);
        $hh->update(['address_id' => $address->address_id]);
        echo "Created and linked new Address ID {$address->address_id} for Household {$code}\n";
        $linkedCount++;
    }
}

fclose($file);
echo "Address repair completed. Recreated: {$recreatedCount}, Linked: {$linkedCount}.\n";
