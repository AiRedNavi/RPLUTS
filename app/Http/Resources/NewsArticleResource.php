<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'country' => $this->whenLoaded('country', fn () => $this->country ? [
                'id' => $this->country->id,
                'name' => $this->country->name,
                'iso_code' => $this->country->iso_code,
            ] : null),
            'title' => $this->title,
            'summary' => $this->summary,
            'source_name' => $this->source_name,
            'source_url' => $this->source_url,
            'category' => $this->category,
            'sentiment_label' => $this->sentiment_label,
            'positive_score' => $this->positive_score,
            'negative_score' => $this->negative_score,
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}