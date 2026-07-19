<?php

namespace App\Console\Commands;

use App\Services\ExternalApi\WorldBankService;
use Illuminate\Console\Command;

class FetchEconomicIndicators extends Command
{
    /**
     * php artisan fetch:economic-indicators
     */
    protected $signature = 'fetch:economic-indicators';

    protected $description = 'Ambil/update GDP, inflasi, populasi, ekspor, impor dari World Bank API';

    public function handle(WorldBankService $service): int
    {
        $this->info('Mengambil indikator ekonomi dari World Bank API...');
        $this->warn('Proses ini bisa memakan waktu karena hit API per negara per indikator.');

        $synced = $service->syncAllCountries();

        $this->info("Selesai. {$synced} negara berhasil disinkronkan.");

        return self::SUCCESS;
    }
}
