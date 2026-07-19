<?php

namespace App\Console\Commands;

use App\Services\ExternalApi\OpenMeteoService;
use Illuminate\Console\Command;

class FetchWeatherData extends Command
{
    /**
     * php artisan fetch:weather
     */
    protected $signature = 'fetch:weather';

    protected $description = 'Ambil/update data cuaca terkini per negara dari Open-Meteo API';

    public function handle(OpenMeteoService $service): int
    {
        $this->info('Mengambil data cuaca dari Open-Meteo API...');

        $synced = $service->syncAllCountries();

        $this->info("Selesai. {$synced} negara berhasil disinkronkan.");

        return self::SUCCESS;
    }
}
