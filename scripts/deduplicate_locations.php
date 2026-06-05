<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Traits\NormalizesLocationNames;

class DeduplicateLocations {
    use NormalizesLocationNames;

    public function run() {
        DB::beginTransaction();

        try {
            echo "Starting location deduplication and merging...\n";

            // 1. Merge duplicate Region "Region VII" (ID 1) into "Central Visayas (Region VII)" (ID 2)
            $hasRegion1 = DB::table('regions')->where('region_id', 1)->exists();
            $hasRegion2 = DB::table('regions')->where('region_id', 2)->exists();

            if ($hasRegion1 && $hasRegion2) {
                echo "Merging Region VII (ID 1) into Central Visayas (Region VII) (ID 2)...\n";
                // Update provinces to point to Region 2
                DB::table('provinces')->where('region_id', 1)->update(['region_id' => 2]);
                // Delete Region 1
                DB::table('regions')->where('region_id', 1)->delete();
                echo "Region merged successfully.\n";
            }

            // 2. General Deduplication: Provinces
            $this->deduplicateTable('provinces', 'province_id', 'province_name', ['region_id'], function($keepId, $dupId) {
                DB::table('cities')->where('province_id', $dupId)->update(['province_id' => $keepId]);
            });

            // 3. General Deduplication: Cities
            $this->deduplicateTable('cities', 'city_id', 'city_name', ['province_id'], function($keepId, $dupId) {
                DB::table('barangays')->where('city_id', $dupId)->update(['city_id' => $keepId]);
                DB::table('zipcodes')->where('city_id', $dupId)->update(['city_id' => $keepId]);
            });

            // 4. General Deduplication: Barangays
            $this->deduplicateTable('barangays', 'barangay_id', 'barangay_name', ['city_id'], function($keepId, $dupId) {
                DB::table('sitios')->where('barangay_id', $dupId)->update(['barangay_id' => $keepId]);
                DB::table('addresses')->where('barangay_id', $dupId)->update(['barangay_id' => $keepId]);
                DB::table('affected_areas')->where('barangay_id', $dupId)->update(['barangay_id' => $keepId]);
                DB::table('analytics')->where('barangay_id', $dupId)->update(['barangay_id' => $keepId]);
            });

            // 5. General Deduplication: Sitios
            $this->deduplicateTable('sitios', 'sitio_id', 'sitio_name', ['barangay_id'], function($keepId, $dupId) {
                DB::table('addresses')->where('sitio_id', $dupId)->update(['sitio_id' => $keepId]);
                DB::table('affected_areas')->where('sitio_id', $dupId)->update(['sitio_id' => $keepId]);
                DB::table('puroks')->where('sitio_id', $dupId)->update(['sitio_id' => $keepId]);
            });

            DB::commit();
            echo "Successfully completed location deduplication!\n";
        } catch (\Throwable $e) {
            DB::rollBack();
            echo "ERROR: " . $e->getMessage() . "\n";
        }
    }

    private function deduplicateTable(string $table, string $idCol, string $nameCol, array $parentCols, callable $onMerge) {
        echo "Processing deduplication for table [{$table}]...\n";
        $rows = DB::table($table)->get();

        // Group rows by parent cols + normalized name
        $groups = [];
        foreach ($rows as $row) {
            $nameVal = $row->{$nameCol} ?? '';
            $normalizedName = strtolower(trim(self::normalizeLocationName($nameVal)));

            $parentKeyParts = [];
            foreach ($parentCols as $pCol) {
                $parentKeyParts[] = $pCol . ':' . ($row->{$pCol} ?? 'null');
            }
            $parentKey = implode('|', $parentKeyParts);
            $key = $parentKey . '||' . $normalizedName;

            $groups[$key][] = $row;
        }

        $mergeCount = 0;
        foreach ($groups as $key => $group) {
            if (count($group) <= 1) {
                continue;
            }

            // We have duplicates! Choose one to keep.
            // Sort criteria: has a code column and code is not null first, then by lower ID
            usort($group, function($a, $b) use ($idCol) {
                $hasCodeA = isset($a->code) && !empty($a->code);
                $hasCodeB = isset($b->code) && !empty($b->code);
                if ($hasCodeA !== $hasCodeB) {
                    return $hasCodeB <=> $hasCodeA; // True first
                }
                // Fallback to lowest ID
                return $a->{$idCol} <=> $b->{$idCol};
            });

            $keepRecord = $group[0];
            $keepId = $keepRecord->{$idCol};

            echo "  Group key [{$key}] has " . count($group) . " entries. Keeping ID: {$keepId} ('{$keepRecord->{$nameCol}}')\n";

            for ($i = 1; $i < count($group); $i++) {
                $dupRecord = $group[$i];
                $dupId = $dupRecord->{$idCol};
                echo "    Merging ID: {$dupId} ('{$dupRecord->{$nameCol}}') into {$keepId}...\n";

                // Execute the foreign key reference updates
                $onMerge($keepId, $dupId);

                // Delete the duplicate record
                DB::table($table)->where($idCol, $dupId)->delete();
                $mergeCount++;
            }
        }

        echo "Table [{$table}] processed. Merged/removed {$mergeCount} duplicate rows.\n\n";
    }
}

(new DeduplicateLocations())->run();
