<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\HouseholdCsvImportService;
use App\Models\User;

$sitios = [
    ['name' => 'Sitio Viking', 'location' => 'Ipil-Ipil Road', 'puroks' => 3],
    ['name' => 'Sitio Alaska', 'location' => 'Ybanez Compound Road', 'puroks' => 6],
    ['name' => 'Sitio Alaska Proper', 'location' => 'Alaska Proper Road', 'puroks' => 2],
    ['name' => 'Sitio Alaska Sentro', 'location' => 'Alaska Sentro Road', 'puroks' => 2],
    ['name' => 'Sitio Kahuyan', 'location' => 'Ybanez Compound Road', 'puroks' => 3],
    ['name' => 'Sitio Abya', 'location' => 'Alaska Road East', 'puroks' => 2],
    ['name' => 'Sitio Wangyu', 'location' => 'Mambaling Inland Road', 'puroks' => 3],
    ['name' => 'Sitio Puntod', 'location' => 'Alaska Shoreline', 'puroks' => 2],
    ['name' => 'Sitio Pagtinabangay', 'location' => 'Alaska Road Network', 'puroks' => 2],
    ['name' => 'Sitio Ybañez', 'location' => 'Ybanez Compound Road', 'puroks' => 3],
    ['name' => 'Sitio Ipil-Ipil', 'location' => 'Ipil-Ipil Road', 'puroks' => 3],
    ['name' => 'Sitio Tangke', 'location' => 'Labangon Boundary Road', 'puroks' => 2],
    ['name' => 'Sitio Huyong-Huyong', 'location' => 'Alaska Inner Road', 'puroks' => 2],
    ['name' => 'Sitio San Juan', 'location' => 'Mambaling Central Road', 'puroks' => 2],
    ['name' => 'Sitio Pagatpat', 'location' => 'Iglesia ni Cristo Road', 'puroks' => 2],
    ['name' => 'Sitio Lawis', 'location' => 'Alaska Coastal Strip', 'puroks' => 3],
    ['name' => 'Sitio Badjao A', 'location' => 'SRP Shoreline East', 'puroks' => 2],
    ['name' => 'Sitio Badjao B', 'location' => 'SRP Shoreline West', 'puroks' => 2],
    ['name' => 'Sitio Tugas', 'location' => 'Tugas Chapel Road', 'puroks' => 2],
    ['name' => 'Sitio Manga', 'location' => 'N. Bacalso Highway', 'puroks' => 2],
];

$firstNames = ['Juan', 'Maria', 'Pedro', 'Ana', 'Jose', 'Rosa', 'Carlos', 'Teresa', 'Luis', 'Carmen', 'Miguel', 'Elena', 'Francisco', 'Luz', 'Antonio', 'Juana', 'Manuel', 'Dolores', 'Jesus', 'Francisca', 'Ricardo', 'Lourdes', 'Roberto', 'Gloria', 'Eduardo', 'Patricia', 'Jaime', 'Alicia', 'Fernando', 'Sylvia'];
$lastNames = ['Cruz', 'Bautista', 'Ocampo', 'Garcia', 'Mendoza', 'Torres', 'Tomas', 'Gonzales', 'Aquino', 'Ramos', 'Lopez', 'Castro', 'Villanueva', 'Santiago', 'Rivera', 'Del Rosario', 'Flores', 'Mercado', 'Dizon', 'Salazar', 'Guzman', 'Hernandez', 'Pascual', 'Valenzuela', 'Guerrero', 'Roxas', 'Ortega'];

// Clean up existing Mambaling (barangay_id: 396) households to avoid unique constraint violations
echo "Cleaning up existing Mambaling households and members...\n";
$barangayId = 396;

try {
    \Illuminate\Support\Facades\DB::table('member_vulnerable_groups')
        ->whereIn('member_id', function ($query) use ($barangayId) {
            $query->select('member_id')
                ->from('household_members')
                ->join('households', 'household_members.household_id', '=', 'households.household_id')
                ->join('addresses', 'households.address_id', '=', 'addresses.address_id')
                ->where('addresses.barangay_id', $barangayId);
        })->delete();

    \Illuminate\Support\Facades\DB::table('household_members')
        ->whereIn('household_id', function ($query) use ($barangayId) {
            $query->select('household_id')
                ->from('households')
                ->join('addresses', 'households.address_id', '=', 'addresses.address_id')
                ->where('addresses.barangay_id', $barangayId);
        })->delete();

    \Illuminate\Support\Facades\DB::table('users')
        ->whereIn('household_id', function ($query) use ($barangayId) {
            $query->select('household_id')
                ->from('households')
                ->join('addresses', 'households.address_id', '=', 'addresses.address_id')
                ->where('addresses.barangay_id', $barangayId);
        })->delete();

    $deletedHouseholds = \Illuminate\Support\Facades\DB::table('households')
        ->whereIn('address_id', function ($query) use ($barangayId) {
            $query->select('address_id')
                ->from('addresses')
                ->where('barangay_id', $barangayId);
        })->delete();
        
    echo "Deleted {$deletedHouseholds} old households.\n";
} catch (\Throwable $e) {
    echo "Cleanup warning: " . $e->getMessage() . "\n";
}

$csvFile = __DIR__ . '/mambaling_households.csv';
$fh = fopen($csvFile, 'w');

// Write CSV Header
fputcsv($fh, [
    'head_first_name',
    'head_middle_name',
    'head_last_name',
    'household_code',
    'email',
    'street',
    'purok_sitio',
    'barangay',
    'contact_number',
    'emergency_contact',
    'member_first_name',
    'member_middle_name',
    'member_last_name',
    'member_birth_date',
    'member_sex',
    'member_relation',
    'member_civil_status',
    'member_education_level',
    'member_occupation',
    'member_is_pwd',
    'member_is_pregnant',
    'head_birth_date',
    'head_sex',
    'head_is_pwd',
    'head_is_pregnant',
    'household_name'
]);

$householdIndex = 1;

foreach ($sitios as $sitio) {
    for ($i = 1; $i <= 10; $i++) {
        $code = sprintf('MAMB-HH-%04d', $householdIndex);
        $hhName = "{$lastNames[array_rand($lastNames)]} Family";
        $email = strtolower(sprintf('%s.%s@mambaling.local', str_replace(' ', '', $hhName), $code));
        
        $purokNum = ($i % $sitio['puroks']) + 1;
        $purokSitioVal = "Purok {$purokNum}, {$sitio['name']}";
        $contact = '0917' . sprintf('%07d', rand(0, 9999999));
        
        // Pick one member index to force vulnerability on (0 = Head, 1 = Member 2, 2 = Member 3)
        $vulnerableIndex = rand(0, 2);
        
        $members = [];
        for ($m = 0; $m < 3; $m++) {
            $fn = $firstNames[array_rand($firstNames)];
            $ln = $lastNames[array_rand($lastNames)];
            $mn = $lastNames[array_rand($lastNames)];
            $sex = (rand(1, 10) > 5) ? 'M' : 'F';
            $age = rand(18, 55); // Default age range
            $isPwd = 'N';
            $isPregnant = 'N';
            
            // Apply forced vulnerability if this is the chosen index
            if ($m === $vulnerableIndex) {
                $vTypes = ($m === 0) ? ['senior', 'pwd', 'pregnant'] : ['child', 'senior', 'pwd', 'pregnant'];
                $vType = $vTypes[array_rand($vTypes)];
                
                if ($vType === 'child') {
                    $age = rand(1, 17);
                } elseif ($vType === 'senior') {
                    $age = rand(60, 82);
                } elseif ($vType === 'pwd') {
                    $isPwd = 'Y';
                } elseif ($vType === 'pregnant') {
                    $sex = 'F';
                    $age = rand(18, 44);
                    $isPregnant = 'Y';
                }
            }
            
            $birthYear = date('Y') - $age;
            $birthDate = sprintf('%04d-%02d-%02d', $birthYear, rand(1, 12), rand(1, 28));
            
            if ($m === 0) {
                $relation = 'Head';
            } else if ($m === 1) {
                $relation = ($age > 18) ? 'Spouse' : 'Child';
            } else {
                $relation = 'Child';
            }
            
            $members[] = [
                'first_name' => $fn,
                'middle_name' => $mn,
                'last_name' => $ln,
                'birth_date' => $birthDate,
                'sex' => $sex,
                'relation' => $relation,
                'is_pwd' => $isPwd,
                'is_pregnant' => $isPregnant,
                'age' => $age
            ];
        }
        
        // Write the 3 rows for this household
        // Row 1: Head row (contains both head details and first member details)
        $head = $members[0];
        fputcsv($fh, [
            $head['first_name'],       // head_first_name (0)
            $head['middle_name'],      // head_middle_name (1)
            $head['last_name'],        // head_last_name (2)
            $code,                     // household_code (3)
            $email,                    // email (4)
            $sitio['location'],        // street (5)
            $purokSitioVal,            // purok_sitio (6)
            'Mambaling',               // barangay (7)
            $contact,                  // contact_number (8)
            '',                        // emergency_contact (9)
            $head['first_name'],       // member_first_name (10)
            $head['middle_name'],      // member_middle_name (11)
            $head['last_name'],        // member_last_name (12)
            $head['birth_date'],       // member_birth_date (13)
            $head['sex'],              // member_sex (14)
            $head['relation'],         // member_relation (15)
            'Single',                  // member_civil_status (16)
            'High School',             // member_education_level (17)
            '',                        // member_occupation (18)
            $head['is_pwd'],           // member_is_pwd (19)
            $head['is_pregnant'],      // member_is_pregnant (20)
            $head['birth_date'],       // head_birth_date (21)
            $head['sex'],              // head_sex (22)
            $head['is_pwd'],           // head_is_pwd (23)
            $head['is_pregnant'],      // head_is_pregnant (24)
            $hhName                    // household_name (25)
        ]);
        
        // Row 2: Member 2
        $m2 = $members[1];
        fputcsv($fh, [
            '', '', '', '', '', '', '', '', '', '', // head and household columns empty
            $m2['first_name'],         // member_first_name (10)
            $m2['middle_name'],        // member_middle_name (11)
            $m2['last_name'],          // member_last_name (12)
            $m2['birth_date'],         // member_birth_date (13)
            $m2['sex'],                // member_sex (14)
            $m2['relation'],           // member_relation (15)
            'Single',                  // member_civil_status (16)
            'High School',             // member_education_level (17)
            '',                        // member_occupation (18)
            $m2['is_pwd'],             // member_is_pwd (19)
            $m2['is_pregnant'],        // member_is_pregnant (20)
            '', '', '', '', ''         // head birth date/sex/pwd/pregnant/name empty
        ]);
        
        // Row 3: Member 3
        $m3 = $members[2];
        fputcsv($fh, [
            '', '', '', '', '', '', '', '', '', '', // head and household columns empty
            $m3['first_name'],         // member_first_name (10)
            $m3['middle_name'],        // member_middle_name (11)
            $m3['last_name'],          // member_last_name (12)
            $m3['birth_date'],         // member_birth_date (13)
            $m3['sex'],                // member_sex (14)
            $m3['relation'],           // member_relation (15)
            'Single',                  // member_civil_status (16)
            'High School',             // member_education_level (17)
            '',                        // member_occupation (18)
            $m3['is_pwd'],             // member_is_pwd (19)
            $m3['is_pregnant'],        // member_is_pregnant (20)
            '', '', '', '', ''         // head birth date/sex/pwd/pregnant/name empty
        ]);
        
        $householdIndex++;
    }
}

fclose($fh);
echo "Generated {$csvFile} successfully with " . (($householdIndex - 1) * 3) . " member rows for " . ($householdIndex - 1) . " households.\n";

// Programmatically run the import
$captain = User::where('email', 'captain@safetrack.local')->first();
if (!$captain) {
    echo "Error: Captain user not found. Seeding first...\n";
    exit(1);
}

$importService = new HouseholdCsvImportService();
$result = $importService->import($csvFile, $captain->user_id);

echo "Import complete: " . json_encode($result) . "\n";
unlink($csvFile); // Clean up the CSV file
