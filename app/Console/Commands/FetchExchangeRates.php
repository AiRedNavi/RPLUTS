<?php

namespace App\Console\Commands;

use App\Services\ExternalApi\ExchangeRateService;
use Illuminate\Console\Command;

class FetchExchangeRates extends Command
{
    /**
     * php artisan fetch:exchange-rates
     * php artisan fetch:exchange-rates --base=EUR
     */
    protected $signature = 'fetch:exchange-rates {--base=USD : Kode mata uang basis, mis. USD atau EUR}';

    protected $description = 'Ambil/update kurs mata uang terkini dari ExchangeRate API';

    public function handle(ExchangeRateService $service): int
    {
        $base = strtoupper($this->option('base'));

        $this->info("Mengambil kurs mata uang dari ExchangeRate API (basis: {$base})...");

        $synced = $service->syncFromBase($base);

        if ($synced === 0) {
            $this->error('Tidak ada kurs yang tersinkron. Cek EXCHANGERATE_API_KEY di .env dan pastikan currency basis ada di database.');

            return self::FAILURE;
        }

        $this->info("Selesai. {$synced} pasangan mata uang berhasil disinkronkan.");

        return self::SUCCESS;
    }
}
