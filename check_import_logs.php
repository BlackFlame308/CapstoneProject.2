<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$logs = DB::table('import_logs')->get();

echo "Import Logs:\n";
foreach ($logs as $log) {
    echo "Row {$log->row_number}: {$log->status} - {$log->error_message}\n";
}
