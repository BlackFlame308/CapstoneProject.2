<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test region
        $region = Region::firstOrCreate([
            'name' => 'Test Region'
        ]);

        // Create a test province
        $province = Province::firstOrCreate([
            'name' => 'Test Province',
            'region_id' => $region->id
        ]);

        // Create a test city
        $city = City::firstOrCreate([
            'name' => 'Test City',
            'province_id' => $province->id
        ]);

        // Create test barangays
        Barangay::firstOrCreate([
            'name' => 'Test Barangay 1',
            'city_id' => $city->id
        ]);

        Barangay::firstOrCreate([
            'name' => 'Test Barangay 2',
            'city_id' => $city->id
        ]);
    }
}