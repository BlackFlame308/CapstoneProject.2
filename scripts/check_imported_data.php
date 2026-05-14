<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$households = DB::table('households')->count();
$members = DB::table('members')->count();
$addresses = DB::table('addresses')->count();
$users = DB::table('users')->count();
$dataSources = DB::table('data_sources')->count();
$csvUploads = DB::table('csv_uploads')->count();

echo "Imported data summary:\n";
echo "Households: $households\n";
echo "Members: $members\n";
echo "Addresses: $addresses\n";
echo "Users (including seeded): $users\n";
echo "Data Sources: $dataSources\n";
echo "CSV Uploads: $csvUploads\n";
