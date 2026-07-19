<?php

namespace App\Services\ExternalApi;

use App\Models\ApiFetchLog;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\ExchangeRateHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExchangeRateService
{
    /**
     * Butuh API key gratis dari https://www.exchangerate-api.com/
     * Simpan di .env sebagai EXCHANGERATE_API_KEY, lalu daftarkan di
     * config/services.php:
     *   'exchangerate' => ['key' => env('EXCHANGERATE_API_KEY')],
     */
    protected function apiKey(): string
    {
        return config('services.exchangerate.key');
    }

    protected string $baseUrl = 'https://v6.exchangerate-api.com/v6';

    /**
     * Sinkronkan kurs dari 1 mata uang basis (default USD) ke semua
     * mata uang lain yang sudah ada di tabel currencies.
     * Dipanggil dari Command: php artisan fetch:exchange-rates
     */
    public function syncFromBase(string $baseCode = 'USD'): int
    {
        $startedAt = microtime(true);
        $synced = 0;

        $baseCurrency = Currency::where('code', $baseCode)->first();

        if (! $baseCurrency) {
            Log::warning("ExchangeRateService: base currency {$baseCode} tidak ditemukan di database.");

            return 0;
        }

        $endpoint = "/{$this->apiKey()}/latest/{$baseCode}";

        try {
            $response = Http::timeout(30)->get("{$this->baseUrl}{$endpoint}");

            ApiFetchLog::record(
                'ExchangeRate API',
                "/latest/{$baseCode}",
                $response->status(),
                (int) ((microtime(true) - $startedAt) * 1000)
            );

            if (! $response->successful() || $response->json('result') !== 'success') {
                Log::warning('ExchangeRateService: gagal fetch kurs', [
                    'status' => $response->status(),
                ]);

                return 0;
            }

            $rates = $response->json('conversion_rates', []);
            $today = now()->toDateString();

            foreach ($rates as $targetCode => $rate) {
                $targetCurrency = Currency::where('code', $targetCode)->first();

                if (! $targetCurrency || $targetCurrency->id === $baseCurrency->id) {
                    continue; // hanya sinkron mata uang yang sudah terdaftar di sistem
                }

                ExchangeRate::updateOrCreate(
                    [
                        'base_currency_id' => $baseCurrency->id,
                        'target_currency_id' => $targetCurrency->id,
                    ],
                    ['rate' => $rate, 'fetched_at' => now()]
                );

                ExchangeRateHistory::updateOrCreate(
                    [
                        'base_currency_id' => $baseCurrency->id,
                        'target_currency_id' => $targetCurrency->id,
                        'recorded_date' => $today,
                    ],
                    ['rate' => $rate]
                );

                $synced++;
            }
        } catch (Throwable $e) {
            Log::error('ExchangeRateService error: '.$e->getMessage());
            ApiFetchLog::record('ExchangeRate API', $endpoint, null, null);
        }

        return $synced;
    }
}
