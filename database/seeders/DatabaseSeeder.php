<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Urutan pemanggilan penting: currencies harus ada dulu sebelum
     * countries (FK currency_id), dan risk_weights/sentiment words
     * berdiri sendiri jadi bisa kapan saja. AdminUserSeeder ditaruh
     * di akhir supaya urutan logis: master data dulu, baru akun.
     */
    public function run(): void
    {
        $this->call([
            CurrencySeeder::class,
            CountrySeeder::class,
            SentimentWordSeeder::class,
            RiskWeightSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
