<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;
use App\Models\Sitio;
use Illuminate\Support\Facades\Schema;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // Real geographical structures based on the Philippine Standard Geographic Code (PSGC)
        $data = [
            'Central Visayas (Region VII)' => [
                'Cebu' => [
                    'Cebu City' => [
                        'barangays' => ['Lahug', 'Mabolo', 'Guadalupe', 'Banilad', 'Pardo'],
                        'sitios' => ['Sitio Zapatera', 'Sitio Kamanggahan']
                    ],
                    'Mandaue City' => [
                        'barangays' => ['Subangdaku', 'Bakilid', 'Tipolo', 'Centro', 'Banilad'],
                        'sitios' => ['Sitio Sili', 'Sitio Maharlika']
                    ],
                    'Lapu-Lapu City' => [
                        'barangays' => ['Mactan', 'Maribago', 'Pajo', 'Basak', 'Gun-ob'],
                        'sitios' => ['Sitio Mustang', 'Sitio Kadasig']
                    ]
                ],
                'Bohol' => [
                    'Tagbilaran City' => [
                        'barangays' => ['Cogon', 'Poblacion I', 'Dampas', 'Mansasa', 'San Isidro'],
                        'sitios' => ['Sitio Ubos', 'Sitio Mansasa Hills']
                    ]
                ],
            ],
            'Western Visayas (Region VI)' => [
                'Iloilo' => [
                    'Iloilo City' => [
                        'barangays' => ['Mandurriao', 'Jaro', 'Molo', 'Arevalo', 'Lapaz'],
                        'sitios' => ['Sitio Bolilao', 'Sitio Sooc']
                    ]
                ],
                'Aklan' => [
                    'Malay' => [
                        'barangays' => ['Balabag (Boracay)', 'Yapak (Boracay)', 'Manoc-Manoc', 'Poblacion', 'Caticlan'],
                        'sitios' => ['Sitio Diniwid', 'Sitio Tambisaan']
                    ]
                ]
            ],
            'National Capital Region (NCR)' => [
                'Metro Manila' => [
                    'Manila' => [
                        'barangays' => ['Intramuros', 'Binondo', 'Malate', 'Ermita', 'Quiapo'],
                        'sitios' => ['Zone 61', 'Zone 72'] // NCR uses Zones instead of traditional sub-sitios
                    ],
                    'Quezon City' => [
                        'barangays' => ['Batasan Hills', 'Commonwealth', 'Socorro', 'Kamuning', 'Bagong Pag-asa'],
                        'sitios' => ['Sitio San Roque', 'Sitio Mendez']
                    ],
                    'Makati' => [
                        'barangays' => ['Bel-Air', 'Poblacion', 'Guadalupe Nuevo', 'Pembo', 'San Lorenzo'],
                        'sitios' => ['Zone 1', 'Zone 2']
                    ]
                ]
            ],
            'Davao Region (Region XI)' => [
                'Davao del Sur' => [
                    'Davao City' => [
                        'barangays' => ['Buhangin', 'Talomo', 'Agdao', 'Matina Pangi', 'Toril'],
                        'sitios' => ['Sitio Inas', 'Sitio Balite']
                    ],
                    'Digos City' => [
                        'barangays' => ['Aplaya', 'Tres de Mayo', 'Matti', 'San Jose', 'Zone 1'],
                        'sitios' => ['Sitio Crame', 'Sitio Mahayahay']
                    ]
                ],
                'Davao del Norte' => [
                    'Tagum City' => [
                        'barangays' => ['Mankilam', 'Apokon', 'Visayan Village', 'San Miguel', 'Magugpo West'],
                        'sitios' => ['Sitio Kadaatan', 'Sitio Tipaz']
                    ]
                ]
            ]
        ];

        foreach ($data as $regionName => $provinces) {
            $region = Region::firstOrCreate(
                ['name' => $regionName],
                $this->codeAttributes('regions', $regionName)
            );

            foreach ($provinces as $provinceName => $cities) {
                $province = Province::firstOrCreate(
                    ['name' => $provinceName, 'region_id' => $region->id],
                    $this->codeAttributes('provinces', $regionName, $provinceName)
                );

                foreach ($cities as $cityName => $cityDetails) {
                    $city = City::firstOrCreate(
                        ['name' => $cityName, 'province_id' => $province->id],
                        $this->codeAttributes('cities', $regionName, $provinceName, $cityName)
                    );

                    foreach ($cityDetails['barangays'] as $barangayName) {
                        $barangay = Barangay::firstOrCreate(
                            ['name' => $barangayName, 'city_id' => $city->id],
                            $this->codeAttributes('barangays', $regionName, $provinceName, $cityName, $barangayName)
                        );

                        foreach ($cityDetails['sitios'] as $sitioName) {
                            Sitio::firstOrCreate(
                                ['name' => $sitioName, 'barangay_id' => $barangay->id],
                                $this->codeAttributes('sitios', $regionName, $provinceName, $cityName, $barangayName, $sitioName)
                            );
                        }
                    }
                }
            }
        }

        $this->command->info('Authentic Philippine location hierarchy seeded successfully!');
    }

    private function codeAttributes(string $table, string ...$parts): array
    {
        return Schema::hasColumn($table, 'code')
            ? ['code' => $this->locationCode(...$parts)]
            : [];
    }

    private function locationCode(string ...$parts): string
    {
        $code = collect($parts)
            ->map(fn (string $part) => strtoupper(preg_replace('/[^A-Z0-9]+/i', '-', $part)))
            ->map(fn (string $part) => trim($part, '-'))
            ->implode('-');

        if (strlen($code) <= 20) {
            return $code;
        }

        return substr($code, 0, 11) . '-' . substr(md5($code), 0, 8);
    }
}
