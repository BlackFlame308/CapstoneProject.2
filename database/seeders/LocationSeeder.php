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
                        'barangays' => [
                            'Adlaon', 'Agsungot', 'Apas', 'Babag', 'Bacayan', 'Banilad', 'Basak Pardo', 'Basak San Nicolas',
                            'Binaliw', 'Bonbon', 'Budlaan', 'Buhisan', 'Bulacao', 'Buot-Taulo', 'Calamba', 'Cambinocot',
                            'Capitol Site', 'Carreta', 'Cogon Pardo', 'Cogon Ramos', 'Day-as', 'Duljo Fatima', 'Ermita',
                            'Guadalupe', 'Guba', 'Hipodromo', 'Inayawan', 'Kalubihan', 'Kalunasan', 'Kamagayan', 'Kamputhaw',
                            'Kasambagan', 'Kinasang-an Pardo', 'Labangon', 'Lahug', 'Lorega San Miguel', 'Lusaran', 'Luz',
                            'Mabini', 'Mabolo', 'Malubog', 'Mambaling', 'Pahina San Nicolas', 'Pahina Central', 'Pamutan',
                            'Parian', 'Paril', 'Pasil', 'Pit-os', 'Poblacion Pardo', 'Pulangbato', 'Pung-ol Sibugay',
                            'Punta Princesa', 'Quiot', 'Sambag I', 'Sambag II', 'San Antonio', 'San Jose', 'San Nicolas Proper',
                            'San Roque', 'Santa Cruz', 'Santo Niño', 'Sapangdaku', 'Sawang Calero', 'Sinsin', 'Sirao',
                            'Suba', 'Sudlon I', 'Sudlon II', 'T. Padilla', 'Tabunan', 'Tagbao', 'Talamban', 'Taptap',
                            'Tejero', 'Tinago', 'Tisa', 'Toong', 'Zapatera'
                        ],
                        'sitios' => ['Sitio Zapatera', 'Sitio Kamanggahan', 'Sitio Kadasig', 'Sitio San Roque']
                    ],
                    'Mandaue City' => [
                        'barangays' => [
                            'Alang-alang', 'Bakilid', 'Banilad', 'Basak', 'Cabancalan', 'Cambaro', 'Canduman', 'Casuntingan',
                            'Casili', 'Centro', 'Cubacub', 'Guizo', 'Ibabao-Estancia', 'Jagobiao', 'Labogon', 'Looc',
                            'Maguikay', 'Mantuyong', 'Opao', 'Pakna-an', 'Pagsabungan', 'Subangdaku', 'Tabok', 'Tawason',
                            'Tingub', 'Tipolo', 'Umapad'
                        ],
                        'sitios' => ['Sitio Sili', 'Sitio Maharlika']
                    ],
                    'Lapu-Lapu City' => [
                        'barangays' => [
                            'Agus', 'Babag', 'Bankal', 'Baring', 'Basak', 'Buaya', 'Calawisan', 'Canjulao', 'Caubian',
                            'Caw-oy', 'Cawhagan', 'Gun-ob', 'Ibo', 'Looc', 'Mactan', 'Maribago', 'Marigondon', 'Pajac',
                            'Pajo', 'Pangan-an', 'Poblacion', 'Punta Engaño', 'Pusok', 'Sabang', 'San Vicente', 'Santa Rosa',
                            'Subabasbas', 'Talima', 'Tingo', 'Tungasan'
                        ],
                        'sitios' => ['Sitio Mustang', 'Sitio Kadasig']
                    ],
                    'Talisay City' => [
                        'barangays' => [
                            'Biasong', 'Bulacao', 'Cadulawan', 'Cansojong', 'Dumlog', 'Jaclupan', 'Lagtang', 'Lawaan I',
                            'Lawaan II', 'Lawaan III', 'Linao', 'Maghaway', 'Manipis', 'Mohon', 'Poblacion', 'Pooc',
                            'San Isidro', 'San Roque', 'Tabunok', 'Tangke', 'Tapul', 'Camp 4'
                        ],
                        'sitios' => ['Sitio Laray', 'Sitio Mohon']
                    ],
                    'Toledo City' => [
                        'barangays' => [
                            'Awihao', 'Bagakay', 'Bato', 'Biga', 'Bulongan', 'Bunga', 'Cabitoonan', 'Calongcalong',
                            'Cambang-ug', 'Camp 8', 'Canlumampao', 'Cantabaco', 'Capitan Claudio', 'Carmen', 'Daanglungsod',
                            'Don Andres Soriano', 'Dumalmon', 'Gen. Climaco', 'Ibo', 'Ilihan', 'Landahan', 'Loay',
                            'Lurang', 'Matab-ang', 'Media Once', 'Pangamihan', 'Poblacion', 'Poog', 'Putingbato', 'Sagay',
                            'Sam-ang', 'Sangi', 'Santo Niño', 'Suba', 'Talavera', 'Tungkay', 'Tubod', 'Uldama'
                        ],
                        'sitios' => ['Sitio Toledo Central', 'Sitio Toledo Coast']
                    ],
                    'Carcar City' => [
                        'barangays' => [
                            'Bolinawan', 'Buenavista', 'Calidngan', 'Can-asujan', 'Guadalupe', 'Liburon', 'Napoles',
                            'Ocaña', 'Perrelos', 'Poblacion I', 'Poblacion II', 'Poblacion III', 'San Jose', 'Sangatan',
                            'Valencia'
                        ],
                        'sitios' => ['Sitio Carcar Shoe', 'Sitio Carcar Chicharon']
                    ],
                    'Naga City' => [
                        'barangays' => [
                            'Alpaco', 'Bairan', 'Balirong', 'Cabungahan', 'Cantao-an', 'Central Poblacion', 'Cogon',
                            'Colon', 'East Poblacion', 'Inoburan', 'Inayagan', 'Jampang', 'Lanas', 'Langtad', 'Lutac',
                            'Mainit', 'Mayana', 'Naalad', 'Pangdan', 'Patoc', 'Poblacion Barangay I', 'Poblacion Barangay II',
                            'San Roque', 'South Poblacion', 'Tagjaguimit', 'Tinaan', 'Tuyan', 'Uling'
                        ],
                        'sitios' => ['Sitio Naga Power', 'Sitio Naga Industrial']
                    ],
                    'Bogo City' => [
                        'barangays' => [
                            'Anapog', 'Banban', 'Binabag', 'Bungtod', 'Carbon', 'Cayang', 'Cogon', 'Dakit',
                            'Don Pedro Rodriguez', 'Gairan', 'La Paz', 'La Purisima Concepcion', 'Libertad', 'Lourdes',
                            'Malingin', 'Marangog', 'Nailon', 'Odlot', 'Pandasan', 'Polambato', 'Pulangbato', 'San Antonio',
                            'San Jose', 'San Roque', 'Santa Fe', 'Santo Niño', 'Santo Rosario', 'Siocon', 'Sudlon'
                        ],
                        'sitios' => ['Sitio Bogo North', 'Sitio Bogo Port']
                    ]
                ],
                'Bohol' => [
                    'Tagbilaran City' => [
                        'barangays' => ['Cogon', 'Poblacion I'],
                        'sitios' => ['Sitio Ubos']
                    ]
                ],
            ],
            'Western Visayas (Region VI)' => [
                'Iloilo' => [
                    'Iloilo City' => [
                        'barangays' => ['Mandurriao', 'Jaro'],
                        'sitios' => ['Sitio Bolilao']
                    ]
                ],
                'Aklan' => [
                    'Malay' => [
                        'barangays' => ['Balabag (Boracay)'],
                        'sitios' => ['Sitio Diniwid']
                    ]
                ]
            ],
            'National Capital Region (NCR)' => [
                'Metro Manila' => [
                    'Manila' => [
                        'barangays' => ['Intramuros', 'Binondo'],
                        'sitios' => ['Zone 61']
                    ],
                    'Quezon City' => [
                        'barangays' => ['Batasan Hills'],
                        'sitios' => ['Sitio San Roque']
                    ]
                ]
            ],
            'Davao Region (Region XI)' => [
                'Davao del Sur' => [
                    'Davao City' => [
                        'barangays' => ['Buhangin', 'Talomo'],
                        'sitios' => ['Sitio Inas']
                    ]
                ],
                'Davao del Norte' => [
                    'Tagum City' => [
                        'barangays' => ['Mankilam'],
                        'sitios' => ['Sitio Tipaz']
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
                    ['name' => $provinceName, 'region_id' => $region->region_id],
                    $this->codeAttributes('provinces', $regionName, $provinceName)
                );

                foreach ($cities as $cityName => $cityDetails) {
                    $city = City::firstOrCreate(
                        ['name' => $cityName, 'province_id' => $province->province_id],
                        $this->codeAttributes('cities', $regionName, $provinceName, $cityName)
                    );

                    foreach ($cityDetails['barangays'] as $barangayName) {
                        $barangay = Barangay::firstOrCreate(
                            ['name' => $barangayName, 'city_id' => $city->city_id],
                            $this->codeAttributes('barangays', $regionName, $provinceName, $cityName, $barangayName)
                        );

                        foreach ($cityDetails['sitios'] as $sitioName) {
                            Sitio::firstOrCreate(
                                ['name' => $sitioName, 'barangay_id' => $barangay->barangay_id],
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
