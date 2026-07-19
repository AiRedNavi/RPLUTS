<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\WeatherHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}