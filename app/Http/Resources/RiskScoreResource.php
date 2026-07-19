<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiskScoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'country' => [
                'id' => $this->country?->id,
                'name' => $this->country?->name,
                'iso_code' => $this->country?->iso_code,
            ],
            'breakdown' => [
                'weather_score' => $this->weather_score,
                'inflation_score' => $this->inflation_score,
                'news_score' => $this->news_score,
                'currency_score' => $this->currency_score,
            ],
            'total_score' => $this->total_score,
            'risk_level' => $this->risk_level,
            'calculated_at' => $this->calculated_at?->toIso8601String(),
        ];
    }
}