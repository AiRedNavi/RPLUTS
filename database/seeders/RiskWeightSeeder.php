<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiskWeightSeeder extends Seeder
{
    /**
     * Bobot default sesuai contoh di spesifikasi PDF:
     * Weather 30%, Inflation 20%, News 40%, Currency 10%.
     * Hanya 1 baris yang aktif (is_active = true) pada satu waktu;
     * admin bisa mengubah lewat RiskWeightController tanpa perlu
     * migration ulang.
     */
    public function run(): void
    {
        $exists = DB::table('risk_weights')->where('is_active', true)->exists();

        if (! $exists) {
            DB::table('risk_weights')->insert([
                'weather_weight' => 30.00,
                'inflation_weight' => 20.00,
                'news_weight' => 40.00,
                'currency_weight' => 10.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
