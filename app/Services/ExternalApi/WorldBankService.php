<?php

namespace App\Services\ExternalApi;

use App\Models\ApiFetchLog;
use App\Models\Country;
use App\Models\EconomicIndicator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WorldBankService
{
    protected string $baseUrl = 'https://api.worldbank.org/v2';

    /**
     * Kode indikator World Bank yang dipakai sesuai spesifikasi proyek.
     */
    protected array $indicators = [
        'gdp' => 'NY.GDP.MKTP.CD',
        'inflation_rate' => 'FP.CPI.TOTL.ZG',
        'population' => 'SP.POP.TOTL',
        'export_value' => 'NE.EXP.GNFS.CD',
        'import_value' => 'NE.IMP.GNFS.CD',
    ];

    /**
     * DESAIN PENTING: alih-alih hit API per negara (yang berarti
     * 5 indikator x N negara = ratusan request dan sangat rentan
     * timeout), method ini mengambil 1 indikator untuk SEMUA negara
     * sekaligus dalam 1 request (endpoint country/all/indicator/...).
     * Hasilnya jadi cuma 5 request total, tidak peduli berapa banyak
     * negara ada di database.
     *
     * Dipanggil dari Command: php artisan fetch:economic-indicators
     */
    public function syncAllCountries(): int
    {
        // Kumpulkan dulu semua negara lokal, di-index by iso_code (3 huruf)
        // supaya gampang dicocokkan dengan hasil dari World Bank.
        $localCountries = Country::all()->keyBy('iso_code');

        if ($localCountries->isEmpty()) {
            Log::warning('WorldBankService: tidak ada negara di database. Jalankan fetch:countries dulu.');

            return 0;
        }

        // dataByCountry[ISO3][field] = ['value' => ..., 'year' => ...]
        $dataByCountry = [];

        foreach ($this->indicators as $field => $indicatorCode) {
            $rows = $this->fetchIndicatorForAllCountries($indicatorCode);

            foreach ($rows as $row) {
                $iso3 = $row['countryiso3code'] ?? null;

                if (! $iso3 || ! isset($localCountries[$iso3])) {
                    continue; // lewati negara/region yang tidak ada di database kita
                }

                if (! isset($row['value']) || $row['value'] === null) {
                    continue;
                }

                $dataByCountry[$iso3][$field] = (float) $row['value'];
                $dataByCountry[$iso3]['year'] = (int) $row['date'];
            }
        }

        $synced = 0;

        foreach ($dataByCountry as $iso3 => $data) {
            $country = $localCountries[$iso3];
            $year = $data['year'] ?? ((int) date('Y') - 1);

            EconomicIndicator::updateOrCreate(
                ['country_id' => $country->id, 'year' => $year],
                [
                    'gdp' => $data['gdp'] ?? null,
                    'inflation_rate' => $data['inflation_rate'] ?? null,
                    'population' => $data['population'] ?? null,
                    'export_value' => $data['export_value'] ?? null,
                    'import_value' => $data['import_value'] ?? null,
                    'source' => 'World Bank API',
                ]
            );

            $synced++;
        }

        return $synced;
    }

    /**
     * Ambil 1 indikator untuk SEMUA negara sekaligus dalam 1 request.
     * per_page dibuat besar (400) karena World Bank punya ~217 negara +
     * beberapa puluh region/aggregate, mrnev=1 memastikan cuma 1 baris
     * (tahun terbaru yang tidak kosong) per negara yang dikembalikan.
     *
     * @return array daftar baris {countryiso3code, value, date}
     */
    protected function fetchIndicatorForAllCountries(string $indicatorCode): array
    {
        $startedAt = microtime(true);
        $endpoint = "/country/all/indicator/{$indicatorCode}";

        try {
            $response = Http::timeout(90)
                ->retry(3, 2000) // coba ulang 3x dengan jeda 2 detik; server World Bank kadang lambat
                ->get("{$this->baseUrl}{$endpoint}", [
                    'format' => 'json',
                    'per_page' => 400,
                    'mrnev' => 1, // most recent non-empty value per negara
                ]);

            ApiFetchLog::record(
                'World Bank API',
                $endpoint,
                $response->status(),
                (int) ((microtime(true) - $startedAt) * 1000)
            );

            if (! $response->successful()) {
                Log::warning("WorldBankService: gagal fetch indikator {$indicatorCode}", [
                    'status' => $response->status(),
                ]);

                return [];
            }

            return $response->json()[1] ?? [];
        } catch (Throwable $e) {
            Log::error("WorldBankService error ({$indicatorCode}): ".$e->getMessage());
            ApiFetchLog::record('World Bank API', $endpoint, null, null);

            return [];
        }
    }
}