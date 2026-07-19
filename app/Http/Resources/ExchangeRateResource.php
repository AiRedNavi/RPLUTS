<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeRateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'base_currency' => $this->whenLoaded('baseCurrency', fn () => $this->baseCurrency?->code),
            'target_currency' => $this->whenLoaded('targetCurrency', fn () => $this->targetCurrency?->code),
            'rate' => $this->rate,
            'fetched_at' => $this->fetched_at?->toIso8601String(),
        ];
    }
}