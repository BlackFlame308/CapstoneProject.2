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
        // -------------------------
        // REGIONS
        // -------------------------
        $regions = [
            'Central Visayas',
            'Western Visayas',
            'Eastern Visayas',
            'National Capital Region',
            'Davao Region'
        ];

        foreach ($regions as $regionName) {
            $region = Region::firstOrCreate(['name' => $regionName]);

            // -------------------------
            // PROVINCES PER REGION
            // -------------------------
            $provinces = match ($regionName) {
                'Central Visayas' => ['Cebu', 'Bohol', 'Negros Oriental', 'Siquijor', 'Cebu Island'],
                'Western Visayas' => ['Iloilo', 'Aklan', 'Antique', 'Capiz', 'Guimaras'],
                'Eastern Visayas' => ['Leyte', 'Southern Leyte', 'Samar', 'Northern Samar', 'Eastern Samar'],
                'National Capital Region' => ['Metro Manila', 'Manila District', 'Quezon Area', 'Pasig Area', 'Taguig Area'],
                'Davao Region' => ['Davao del Sur', 'Davao del Norte', 'Davao Oriental', 'Davao de Oro', 'Davao Occidental'],
                default => []
            };

            foreach ($provinces as $provinceName) {
                $province = Province::firstOrCreate([
                    'name' => $provinceName,
                    'region_id' => $region->id
                ]);

                // -------------------------
                // CITIES PER PROVINCE
                // -------------------------
                $cities = match ($provinceName) {

                    // Cebu
                    'Cebu' => ['Cebu City', 'Mandaue City', 'Lapu-Lapu City', 'Talisay City', 'Toledo City'],

                    // Bohol
                    'Bohol' => ['Tagbilaran City', 'Tubigon', 'Talibon', 'Jagna', 'Ubay'],

                    // Negros Oriental
                    'Negros Oriental' => ['Dumaguete City', 'Bayawan City', 'Bais City', 'Tanjay City', 'Guihulngan City'],

                    // Iloilo
                    'Iloilo' => ['Iloilo City', 'Passi City', 'Oton', 'Pavia', 'Santa Barbara'],

                    // NCR
                    'Metro Manila' => ['Manila', 'Quezon City', 'Makati', 'Pasig', 'Taguig'],

                    // Davao del Sur
                    'Davao del Sur' => ['Davao City', 'Digos City', 'Bansalan', 'Hagonoy', 'Padada'],

                    default => ['Sample City A', 'Sample City B', 'Sample City C', 'Sample City D', 'Sample City E']
                };

                foreach ($cities as $cityName) {
                    $city = City::firstOrCreate([
                        'name' => $cityName,
                        'province_id' => $province->id
                    ]);

                    // -------------------------
                    // BARANGAYS PER CITY
                    // -------------------------
                    $barangays = [
                        'Poblacion',
                        'San Isidro',
                        'San Roque',
                        'San Jose',
                        'Barangay 1'
                    ];

                    foreach ($barangays as $barangayName) {
                        Barangay::firstOrCreate([
                            'name' => $barangayName,
                            'city_id' => $city->id
                        ]);
                    }
                }
            }
        }
    }
}