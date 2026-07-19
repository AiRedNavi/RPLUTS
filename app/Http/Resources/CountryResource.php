<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'iso_code' => $this->iso_code,
            'region' => $this->region,
            'capital' => $this->capital,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'language' => $this->language,
            'currency' => $this->whenLoaded('currency', fn () => [
                'code' => $this->currency?->code,
                'name' => $this->currency?->name,
                'symbol' => $this->currency?->symbol,
            ]),
            // Relasi opsional, cuma ikut ditampilkan kalau di-load
            // dengan ->with('weatherSnapshot') atau ->with('riskScore')
            // di controller (hindari N+1 query).
            'weather' => $this->whenLoaded('weatherSnapshot', fn () => $this->weatherSnapshot ? [
                'temperature' => $this->weatherSnapshot->temperature,
                'rainfall' => $this->weatherSnapshot->rainfall,
                'wind_speed' => $this->weatherSnapshot->wind_speed,
                'storm_risk_level' => $this->weatherSnapshot->storm_risk_level,
            ] : null),
            'risk_score' => $this->whenLoaded('riskScore', fn () => $this->riskScore ? [
                'total_score' => $this->riskScore->total_score,
                'risk_level' => $this->riskScore->risk_level,
            ] : null),
            'latest_indicator' => $this->whenLoaded('economicIndicators', function () {
                $latest = $this->economicIndicators->sortByDesc('year')->first();

                return $latest ? [
                    'year' => $latest->year,
                    'gdp' => $latest->gdp,
                    'inflation_rate' => $latest->inflation_rate,
                    'population' => $latest->population,
                    'export_value' => $latest->export_value,
                    'import_value' => $latest->import_value,
                ] : null;
            }),
        ];
    }
}