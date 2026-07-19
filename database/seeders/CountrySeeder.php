<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Data negara contoh untuk demo, sesuai studi kasus di spesifikasi
     * (Germany, China, Indonesia, Australia) ditambah beberapa negara
     * lain yang umum dipakai untuk skenario impor-ekspor.
     *
     * Catatan: di aplikasi nyata, tabel ini idealnya di-refresh otomatis
     * lewat RestCountriesService (lihat FetchCountriesData command).
     * Seeder ini hanya untuk data awal / fallback saat development.
     */
    public function run(): void
    {
        $countries = [
            [
                'name' => 'Germany',
                'iso_code' => 'DEU',
                'region' => 'Europe',
                'capital' => 'Berlin',
                'latitude' => 52.520008,
                'longitude' => 13.404954,
                'language' => 'German',
                'currency_code' => 'EUR',
            ],
            [
                'name' => 'China',
                'iso_code' => 'CHN',
                'region' => 'Asia',
                'capital' => 'Beijing',
                'latitude' => 39.904200,
                'longitude' => 116.407396,
                'language' => 'Mandarin',
                'currency_code' => 'CNY',
            ],
            [
                'name' => 'Indonesia',
                'iso_code' => 'IDN',
                'region' => 'Asia',
                'capital' => 'Jakarta',
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'language' => 'Indonesian',
                'currency_code' => 'IDR',
            ],
            [
                'name' => 'Australia',
                'iso_code' => 'AUS',
                'region' => 'Oceania',
                'capital' => 'Canberra',
                'latitude' => -35.282001,
                'longitude' => 149.128998,
                'language' => 'English',
                'currency_code' => 'AUD',
            ],
            [
                'name' => 'United States',
                'iso_code' => 'USA',
                'region' => 'Americas',
                'capital' => 'Washington, D.C.',
                'latitude' => 38.907192,
                'longitude' => -77.036871,
                'language' => 'English',
                'currency_code' => 'USD',
            ],
            [
                'name' => 'United Kingdom',
                'iso_code' => 'GBR',
                'region' => 'Europe',
                'capital' => 'London',
                'latitude' => 51.507351,
                'longitude' => -0.127758,
                'language' => 'English',
                'currency_code' => 'GBP',
            ],
            [
                'name' => 'Japan',
                'iso_code' => 'JPN',
                'region' => 'Asia',
                'capital' => 'Tokyo',
                'latitude' => 35.689487,
                'longitude' => 139.691711,
                'language' => 'Japanese',
                'currency_code' => 'JPY',
            ],
        ];

        foreach ($countries as $country) {
            $currencyId = DB::table('currencies')
                ->where('code', $country['currency_code'])
                ->value('id');

            DB::table('countries')->updateOrInsert(
                ['iso_code' => $country['iso_code']],
                [
                    'name' => $country['name'],
                    'region' => $country['region'],
                    'capital' => $country['capital'],
                    'latitude' => $country['latitude'],
                    'longitude' => $country['longitude'],
                    'language' => $country['language'],
                    'currency_id' => $currencyId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
