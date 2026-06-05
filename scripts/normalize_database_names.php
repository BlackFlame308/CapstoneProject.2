<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Traits\NormalizesLocationNames;

class NormalizeDatabaseNames {
    use NormalizesLocationNames;

    public function run() {
        $tables = [
            'regions' => ['id' => 'region_id', 'name' => 'region_name'],
            'provinces' => ['id' => 'province_id', 'name' => 'province_name'],
            'cities' => ['id' => 'city_id', 'name' => 'city_name'],
            'barangays' => ['id' => 'barangay_id', 'name' => 'barangay_name'],
            'sitios' => ['id' => 'sitio_id', 'name' => 'sitio_name']
        ];

        DB::beginTransaction();

        try {
            foreach ($tables as $table => $cols) {
                echo "Normalizing table [{$table}]...\n";
                $rows = DB::table($table)->get();
                $updatedCount = 0;

                foreach ($rows as $row) {
                    $id = $row->{$cols['id']};
                    $nameVal = $row->{$cols['name']} ?? null;
                    if ($nameVal === null) continue;

                    $normalized = self::normalizeLocationName($nameVal);
                    if ($nameVal !== $normalized) {
                        DB::table($table)
                            ->where($cols['id'], $id)
                            ->update([$cols['name'] => $normalized]);
                        $updatedCount++;
                    }
                }
                echo "Normalized {$updatedCount} rows in {$table}.\n";
            }

            DB::commit();
            echo "Successfully normalized all database location names!\n";
        } catch (\Throwable $e) {
            DB::rollBack();
            echo "ERROR: " . $e->getMessage() . "\n";
        }
    }
}

(new NormalizeDatabaseNames())->run();
