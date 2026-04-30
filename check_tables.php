<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
echo "Tables in database:\n";
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    echo "- $tableName\n";
}
