<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsArticle extends Model
{
    use HasFactory;

    const UPDATED_AT = null; // tabel ini hanya punya created_at

    protected $fillable = [
        'country_id',
        'title',
        'summary',
        'source_name',
        'source_url',
        'category',
        'sentiment_label',
        'positive_score',
        'negative_score',
        'published_at',
    ];

    protected $casts = [
        'positive_score' => 'integer',
        'negative_score' => 'integer',
        'published_at' => 'datetime',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Dipakai oleh SentimentAnalysisService untuk menyimpan hasil analisis.
     */
    public function setSentiment(string $label, int $positiveScore, int $negativeScore): void
    {
        $this->update([
            'sentiment_label' => $label,
            'positive_score' => $positiveScore,
            'negative_score' => $negativeScore,
        ]);
    }
}
