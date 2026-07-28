<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\WeatherHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\BufferedOutput;
use Throwable;

class WeatherController extends Controller
{
    /**
     * GET /api/weather
     * Cuaca terkini semua negara yang punya data, untuk Global Weather
     * Monitoring map (Leaflet.js). Sengaja dibuat ringan (tanpa
     * pagination) karena datanya dipakai buat plot marker di peta.
     */
    public function index(): JsonResponse
    {
        $countries = Country::with('weatherSnapshot')
            ->whereHas('weatherSnapshot')
            ->get(['id', 'name', 'iso_code', 'latitude', 'longitude']);

        $data = $countries->map(fn (Country $country) => [
            'country' => [
                'id' => $country->id,
                'name' => $country->name,
                'iso_code' => $country->iso_code,
            ],
            'latitude' => $country->latitude,
            'longitude' => $country->longitude,
            'temperature' => $country->weatherSnapshot->temperature,
            'rainfall' => $country->weatherSnapshot->rainfall,
            'wind_speed' => $country->weatherSnapshot->wind_speed,
            'storm_risk_level' => $country->weatherSnapshot->storm_risk_level,
        ]);

        return response()->json(['data' => $data]);
    }

    /**
     * GET /api/weather/{idOrIsoCode}/history
     * Histori cuaca 1 negara untuk grafik tren.
     */
    public function history(string $idOrIsoCode): JsonResponse
    {
        $country = Country::where('id', $idOrIsoCode)
            ->orWhere('iso_code', strtoupper($idOrIsoCode))
            ->first();

        if (! $country) {
            return response()->json(['message' => 'Negara tidak ditemukan.'], 404);
        }

        $history = WeatherHistory::where('country_id', $country->id)
            ->orderBy('recorded_date')
            ->get(['temperature', 'rainfall', 'wind_speed', 'recorded_date']);

        return response()->json(['data' => $history]);
    }

    /**
     * POST /api/weather/fetch
     *
     * Trigger manual untuk narik data cuaca terbaru dari Open-Meteo,
     * dipicu dari tombol di halaman weather-map.blade.php (tanpa perlu
     * jalanin `php artisan fetch:weather` manual di terminal/VSCode).
     *
     * Route ini sengaja TIDAK dipasangi middleware auth supaya bisa
     * diakses guest, user, maupun admin (semua role). Kalau khawatir
     * disalahgunakan/spam, tambahkan middleware throttle di routes/api.php
     * (lihat catatan di route definition).
     *
     * Command `fetch:weather` yang sesungguhnya (di
     * app/Console/Commands/FetchWeatherData.php) yang bertanggung jawab
     * narik data & simpan/upsert ke weather_snapshots + weather_history.
     * Controller ini cuma memicu command tsb, bukan menduplikasi logicnya,
     * supaya command tetap jadi satu-satunya sumber logic fetching
     * (dipakai juga oleh scheduler otomatis).
     */
    public function fetchNow(): JsonResponse
    {
        $bufferedOutput = new BufferedOutput();

        try {
            $exitCode = Artisan::call('fetch:weather', [], $bufferedOutput);

            if ($exitCode !== 0) {
                Log::warning('Command fetch:weather selesai dengan exit code non-zero.', [
                    'exit_code' => $exitCode,
                    'output' => $bufferedOutput->fetch(),
                ]);

                return response()->json([
                    'message' => 'Fetch data cuaca selesai, tapi ada indikasi masalah. Cek log server untuk detail.',
                ], 500);
            }

            return response()->json([
                'message' => 'Data cuaca berhasil diperbarui dari Open-Meteo.',
                'fetched_at' => now()->toDateTimeString(),
            ]);
        } catch (Throwable $e) {
            Log::error('Gagal menjalankan fetch:weather via tombol Global Weather Monitoring.', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Gagal mengambil data cuaca terbaru. Silakan coba lagi beberapa saat lagi.',
            ], 500);
        }
    }
}