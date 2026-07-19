<?php

namespace App\Console\Commands;

use App\Services\ExternalApi\RestCountriesService;
use Illuminate\Console\Command;

class FetchCountriesData extends Command
{
    /**
     * php artisan fetch:countries
     */
    protected $signature = 'fetch:countries';

    protected $description = 'Ambil/update data dasar negara dari REST Countries API';

    public function handle(RestCountriesService $service): int
    {
        $this->info('Mengambil data negara dari REST Countries API...');

        $synced = $service->syncAllCountries();

        $this->info("Selesai. {$synced} negara berhasil disinkronkan.");

        return self::SUCCESS;
    }
}
