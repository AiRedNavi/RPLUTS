<?php

namespace App\Services\ExternalApi;

use App\Models\ApiFetchLog;
use App\Models\Country;
use App\Models\WeatherHistory;
use App\Models\WeatherSnapshot;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OpenMeteoService
{
    protected string $baseUrl = 'https://api.open-meteo.com/v1/forecast';

    /**
     * Sinkronkan cuaca terkini untuk semua negara yang punya koordinat.
     * Dipanggil dari Command: php artisan fetch:weather
     */
    public function syncAllCountries(): int
    {
        $synced = 0;

        $countries = Country::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        foreach ($countries as $country) {
            if ($this->syncCountry($country)) {
                $synced++;
            }
        }

        return $synced;
    }

    /**
     * Ambil cuaca terkini 1 negara (berdasarkan koordinat ibu kota/pusat
     * negara), lalu update weather_snapshots dan catat ke weather_history.
     * Tidak butuh API key.
     */
    public function syncCountry(Country $country): bool
    {
        $startedAt = microtime(true);

        try {
            $response = Http::timeout(30)->get($this->baseUrl, [
                'latitude' => $country->latitude,
                'longitude' => $country->longitude,
                'current' => 'temperature_2m,precipitation,wind_speed_10m',
                'timezone' => 'auto',
            ]);

            ApiFetchLog::record(
                'Open-Meteo API',
                '/v1/forecast',
                $response->status(),
                (int) ((microtime(true) - $startedAt) * 1000)
            );

            if (! $response->successful()) {
                return false;
            }

            $current = $response->json()['current'] ?? null;

            if (! $current) {
                return false;
            }

            $temperature = $current['temperature_2m'] ?? null;
            $rainfall = $current['precipitation'] ?? null;
            $windSpeed = $current['wind_speed_10m'] ?? null;
            $stormRisk = $this->calculateStormRisk($rainfall, $windSpeed);

            WeatherSnapshot::updateOrCreate(
                ['country_id' => $country->id],
                [
                    'temperature' => $temperature,
                    'rainfall' => $rainfall,
                    'wind_speed' => $windSpeed,
                    'storm_risk_level' => $stormRisk,
                    'fetched_at' => now(),
                ]
            );

            WeatherHistory::updateOrCreate(
                ['country_id' => $country->id, 'recorded_date' => now()->toDateString()],
                [
                    'temperature' => $temperature,
                    'rainfall' => $rainfall,
                    'wind_speed' => $windSpeed,
                ]
            );

            return true;
        } catch (Throwable $e) {
            Log::error('OpenMeteoService error: '.$e->getMessage());
            ApiFetchLog::record('Open-Meteo API', '/v1/forecast', null, null);

            return false;
        }
    }

    /**
     * Algoritma sederhana buatan sendiri untuk menentukan level risiko
     * badai dari curah hujan (mm) dan kecepatan angin (km/h).
     * Silakan disesuaikan bobotnya sesuai kebutuhan risk scoring kamu.
     */
    protected function calculateStormRisk(?float $rainfall, ?float $windSpeed): string
    {
        $rainfall ??= 0;
        $windSpeed ??= 0;

        if ($rainfall > 20 || $windSpeed > 60) {
            return 'high';
        }

        if ($rainfall > 5 || $windSpeed > 30) {
            return 'medium';
        }

        return 'low';
    }
}
