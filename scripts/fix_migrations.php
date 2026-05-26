<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$completedMigrations = [
    '2026_04_18_075723_create_roles_table',
    '2026_04_18_075806_create_users_table',
    '2026_04_18_075822_create_locations_hierarchy_tables',
    '2026_04_18_075858_create_addresses_table',
    '2026_04_18_075905_create_households_table',
    '2026_04_18_075926_create_members_table',
    //'2026_04_18_075934_create_data_sources_table',
    //'2026_04_18_075941_create_csv_uploads_table',
    //'2026_04_18_075955_create_import_logs_table',
    //'2026_04_18_080002_create_analytics_table',
];

foreach ($completedMigrations as $migration) {
    DB::table('migrations')->insertOrIgnore([
        'migration' => $migration,
        'batch' => 1
    ]);
}

echo "Updated migrations table.\n";
