<?php

namespace App\Console\Commands;

use App\Services\RiskScoringService;
use Illuminate\Console\Command;

class CalculateRiskScores extends Command
{
    /**
     * php artisan risk:calculate
     */
    protected $signature = 'risk:calculate';

    protected $description = 'Hitung skor risiko semua negara berdasarkan cuaca, inflasi, berita, dan kurs';

    public function handle(RiskScoringService $service): int
    {
        $this->info('Menghitung risk score untuk semua negara...');
        $this->line('Pastikan sudah menjalankan fetch:weather, fetch:economic-indicators,');
        $this->line('fetch:exchange-rates, fetch:news, dan analyze:sentiment sebelum ini,');
        $this->line('supaya hasilnya akurat (bukan cuma nilai netral default).');
        $this->newLine();

        $calculated = $service->calculateForAllCountries();

        if ($calculated === 0) {
            $this->error('Tidak ada negara yang berhasil dihitung. Cek apakah risk_weights sudah ter-seed (RiskWeightSeeder).');

            return self::FAILURE;
        }

        $this->info("Selesai. {$calculated} negara berhasil dihitung risk score-nya.");

        return self::SUCCESS;
    }
}