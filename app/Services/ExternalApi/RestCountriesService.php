<?php

namespace App\Services\ExternalApi;

use App\Models\ApiFetchLog;
use App\Models\Country;
use App\Models\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class RestCountriesService
{
    /**
     * CATATAN PENTING (update Juli 2026):
     * restcountries.com v3.1 sudah resmi deprecated (server mengembalikan
     * error payload, bukan data lagi). Penggantinya, v5, sekarang WAJIB
     * pakai API key (sign up dulu, free tier 500 request/bulan).
     *
     * Supaya proyek ini tetap bisa jalan tanpa perlu daftar API key,
     * dipakai countries.dev sebagai gantinya — alternatif gratis,
     * tanpa API key, tanpa rate limit, dengan cakupan data yang setara
     * (ISO code, region, capital, koordinat, bahasa, mata uang).
     * Dokumentasi: https://countries.dev/docs
     */
    protected string $baseUrl = 'https://countries.dev';

    /**
     * Ambil data dasar semua negara (nama, region, ibu kota, koordinat,
     * bahasa, mata uang) lalu simpan/update ke tabel countries & currencies.
     * Tidak butuh API key.
     *
     * Dipanggil dari Command: php artisan fetch:countries
     */
    public function syncAllCountries(): int
    {
        $startedAt = microtime(true);
        $synced = 0;

        try {
            $response = Http::timeout(30)->get("{$this->baseUrl}/countries", [
                'fields' => 'name,alpha3Code,region,capital,latlng,languages,currencies',
            ]);

            $statusCode = $response->status();

            if ($response->successful()) {
                $items = $response->json();

                if (! is_array($items)) {
                    Log::warning('RestCountriesService: response bukan array seperti yang diharapkan', [
                        'status' => $statusCode,
                        'body_preview' => substr($response->body(), 0, 300),
                    ]);
                } else {
                    foreach ($items as $item) {
                        // Beberapa elemen dari API publik ini kadang tidak
                        // berupa array data negara yang valid (mis. entri
                        // rusak/kosong dari sisi server). Lewati saja
                        // daripada bikin seluruh proses sinkronisasi gagal.
                        if (! is_array($item)) {
                            continue;
                        }

                        if ($this->syncSingleCountry($item)) {
                            $synced++;
                        }
                    }
                }
            } else {
                Log::warning('RestCountriesService: gagal fetch data negara', [
                    'status' => $statusCode,
                    'body_preview' => substr($response->body(), 0, 300),
                ]);
            }

            ApiFetchLog::record(
                'countries.dev (REST Countries alternative)',
                '/countries',
                $statusCode,
                (int) ((microtime(true) - $startedAt) * 1000)
            );
        } catch (Throwable $e) {
            Log::error('RestCountriesService error: '.$e->getMessage());
            ApiFetchLog::record('countries.dev (REST Countries alternative)', '/countries', null, null);
        }

        return $synced;
    }

    /**
     * Simpan satu negara dari payload countries.dev ke database.
     * Mata uang dibuat otomatis di tabel currencies kalau belum ada.
     *
     * Bentuk payload countries.dev (mirip REST Countries versi lama):
     * {
     *   "name": "Germany",
     *   "alpha3Code": "DEU",
     *   "region": "Europe",
     *   "capital": "Berlin",
     *   "latlng": [51, 9],
     *   "languages": [{ "name": "German", ... }],
     *   "currencies": [{ "code": "EUR", "name": "Euro", "symbol": "€" }]
     * }
     */
    protected function syncSingleCountry(array $item): bool
    {
        $isoCode = $item['alpha3Code'] ?? null;
        $name = $item['name'] ?? null;

        if (! $isoCode || ! $name) {
            return false;
        }

        // Ambil mata uang pertama yang tersedia (beberapa negara punya lebih dari 1)
        $currencyId = null;
        if (! empty($item['currencies']) && is_array($item['currencies'])) {
            $currencyData = $item['currencies'][0] ?? null;

            if (! empty($currencyData['code'])) {
                $currency = Currency::updateOrCreate(
                    ['code' => $currencyData['code']],
                    [
                        'name' => $currencyData['name'] ?? $currencyData['code'],
                        'symbol' => $currencyData['symbol'] ?? null,
                    ]
                );
                $currencyId = $currency->id;
            }
        }

        $language = ! empty($item['languages']) && is_array($item['languages'])
            ? implode(', ', array_filter(array_column($item['languages'], 'name')))
            : null;

        Country::updateOrCreate(
            ['iso_code' => $isoCode],
            [
                'name' => $name,
                'region' => $item['region'] ?? null,
                'capital' => $item['capital'] ?? null,
                'latitude' => $item['latlng'][0] ?? null,
                'longitude' => $item['latlng'][1] ?? null,
                'language' => $language,
                'currency_id' => $currencyId,
            ]
        );

        return true;
    }
}