<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\{User, Household, Member, Address, DataSource, CsvUpload, ImportLog};

echo "Starting cleanup..." . PHP_EOL;
DB::statement('SET FOREIGN_KEY_CHECKS=0');

// Clean members
$deletedCount = Member::withTrashed()->where('household_id', 'like', 'SYSTEST-HH%')->forceDelete();
echo "Cleaned members: " . $deletedCount . PHP_EOL;

$deletedCount = Member::withTrashed()->where('first_name', 'like', 'Juan%')->forceDelete();
echo "Cleaned members by name: " . $deletedCount . PHP_EOL;

$deletedCount = Member::withTrashed()->where('first_name', 'like', 'Maria%')->forceDelete();
echo "Cleaned members by child name: " . $deletedCount . PHP_EOL;

// Clean users
$deletedCount = User::where('username', 'test_enc_sys')->forceDelete();
echo "Cleaned test encoders: " . $deletedCount . PHP_EOL;

$deletedCount = User::where('email', 'like', '%@safetrack.local')->where('email', '!=', 'captain@safetrack.local')->where('email', '!=', 'encoder@safetrack.local')->forceDelete();
echo "Cleaned test provisioned users: " . $deletedCount . PHP_EOL;

$deletedCount = User::where('email', 'reyesfamily@test.local')->forceDelete();
echo "Cleaned Reyes family user: " . $deletedCount . PHP_EOL;

// Clean households
$deletedCount = Household::withTrashed()->where('household_id', 'like', 'SYSTEST-HH%')->forceDelete();
echo "Cleaned test households: " . $deletedCount . PHP_EOL;

foreach(['Reyes Family','Santos Family'] as $n) {
    $h = Household::withTrashed()->where('household_name',$n)->first();
    if ($h) {
        User::where('household_id',$h->household_id)->forceDelete();
        Member::withTrashed()->where('household_id',$h->household_id)->forceDelete();
        $h->forceDelete();
        echo "Cleaned CSV household: " . $n . PHP_EOL;
    }
}

// Clean addresses safely (only test addresses and orphan addresses not linked to any households)
$deletedCount = Address::where('purok_sitio', 'Purok Test')
    ->orWhere('street', 'Test St')
    ->delete();

$allAddresses = Address::all();
foreach ($allAddresses as $address) {
    if (!$address->household()->exists()) {
        $address->delete();
        $deletedCount++;
    }
}
echo "Cleaned test addresses: " . $deletedCount . PHP_EOL;

// Clean data sources & import logs
$ds = DataSource::latest()->take(15)->get();
foreach ($ds as $d) {
    ImportLog::where('data_source_id',$d->id)->delete();
    CsvUpload::where('data_source_id',$d->id)->delete();
    $d->delete();
}
echo "Cleaned data sources, csv uploads, and import logs." . PHP_EOL;

DB::statement('SET FOREIGN_KEY_CHECKS=1');
echo "Cleanup finished." . PHP_EOL;
