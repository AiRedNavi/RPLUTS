<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'country' => $this->whenLoaded('country', fn () => $this->country ? [
                'id' => $this->country->id,
                'name' => $this->country->name,
                'iso_code' => $this->country->iso_code,
            ] : null),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'unlocode' => $this->unlocode,
        ];
    }
}