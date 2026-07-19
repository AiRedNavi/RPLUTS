<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    /**
     * Membuat 1 akun admin default untuk keperluan development & demo.
     *
     * PENTING: ganti password ini sebelum proyek dikumpulkan/deploy.
     * Kredensial default sengaja ditaruh di sini (bukan di .env) supaya
     * gampang dijalankan ulang saat migrate:fresh --seed, tapi jangan
     * dipakai apa adanya di lingkungan produksi.
     */
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@supplychainrisk.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password123'), // TODO: ganti sebelum deploy
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
