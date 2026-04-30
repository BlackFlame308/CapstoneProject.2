<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;
use App\Models\Sitio;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Central Visayas' => [
                'Cebu'             => ['Cebu City', 'Mandaue City', 'Lapu-Lapu City', 'Talisay City', 'Toledo City'],
                'Bohol'            => ['Tagbilaran City', 'Tubigon', 'Talibon', 'Jagna', 'Ubay'],
                'Negros Oriental'  => ['Dumaguete City', 'Bayawan City', 'Bais City', 'Tanjay City', 'Guihulngan City'],
                'Siquijor'         => ['Siquijor', 'Larena', 'Enrique Villanueva', 'Lazi', 'Maria'],
            ],
            'Western Visayas' => [
                'Iloilo'   => ['Iloilo City', 'Passi City', 'Oton', 'Pavia', 'Santa Barbara'],
                'Aklan'    => ['Kalibo', 'Ibajay', 'Lezo', 'Makato', 'Malay'],
                'Antique'  => ['San Jose de Buenavista', 'Hamtic', 'Tibiao', 'Barbaza', 'Sibalom'],
                'Capiz'    => ['Roxas City', 'Ivisan', 'Maayon', 'Panay', 'Pontevedra'],
                'Guimaras' => ['Jordan', 'Buenavista', 'Nueva Valencia', 'San Lorenzo', 'Sibunag'],
            ],
            'Eastern Visayas' => [
                'Leyte'          => ['Tacloban City', 'Ormoc City', 'Baybay City', 'Palo', 'Tanauan'],
                'Southern Leyte' => ['Maasin City', 'Macrohon', 'Padre Burgos', 'Sogod', 'Liloan'],
                'Samar'          => ['Catbalogan City', 'Calbayog City', 'Gandara', 'Paranas', 'Zumarraga'],
                'Northern Samar' => ['Catarman', 'Allen', 'Bobon', 'Lavezares', 'Laoang'],
                'Eastern Samar'  => ['Borongan City', 'Guiuan', 'Balangiga', 'Lawaan', 'Salcedo'],
            ],
            'National Capital Region' => [
                'Metro Manila' => ['Manila', 'Quezon City', 'Makati', 'Pasig', 'Taguig'],
            ],
            'Davao Region' => [
                'Davao del Sur'    => ['Davao City', 'Digos City', 'Bansalan', 'Hagonoy', 'Padada'],
                'Davao del Norte'  => ['Tagum City', 'Panabo City', 'Samal City', 'Carmen', 'New Corella'],
                'Davao Oriental'   => ['Mati City', 'Baganga', 'Cateel', 'Boston', 'Caraga'],
                'Davao de Oro'     => ['Nabunturan', 'Montevista', 'Monkayo', 'Compostela', 'Laak'],
                'Davao Occidental' => ['Malita', 'Jose Abad Santos', 'Santa Maria', 'Sarangani', 'Don Marcelino'],
            ],
        ];

        $barangayNames = ['Poblacion', 'San Isidro', 'San Roque', 'San Jose', 'Barangay 1'];
        $sitioNames    = ['Sitio 1', 'Sitio 2'];

        foreach ($data as $regionName => $provinces) {
            $region = Region::firstOrCreate(
                ['name' => $regionName],
                ['code' => strtoupper(str_replace(' ', '-', $regionName))]
            );

            foreach ($provinces as $provinceName => $cities) {
                $province = Province::firstOrCreate(
                    ['name' => $provinceName, 'region_id' => $region->id],
                    ['code' => strtoupper(str_replace(' ', '-', $provinceName))]
                );

                foreach ($cities as $cityName) {
                    $city = City::firstOrCreate(
                        ['name' => $cityName, 'province_id' => $province->id]
                    );

                    foreach ($barangayNames as $barangayName) {
                        $barangay = Barangay::firstOrCreate(
                            ['name' => $barangayName, 'city_id' => $city->id]
                        );

                        foreach ($sitioNames as $sitioName) {
                            Sitio::firstOrCreate(
                                ['name' => $sitioName, 'barangay_id' => $barangay->id]
                            );
                        }
                    }
                }
            }
        }

        $this->command->info('Location hierarchy seeded: Regions > Provinces > Cities > Barangays > Sitios');
    }
}
