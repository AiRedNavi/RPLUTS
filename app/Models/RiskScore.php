<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskScore extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'country_id',
        'weather_score',
        'inflation_score',
        'news_score',
        'currency_score',
        'total_score',
        'risk_level',
        'calculated_at',
    ];

    protected $casts = [
        'weather_score' => 'decimal:2',
        'inflation_score' => 'decimal:2',
        'news_score' => 'decimal:2',
        'currency_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Tentukan risk_level dari total_score.
     * Threshold: 0-33 Low, 34-66 Medium, 67-100 High.
     * Silakan disesuaikan dengan algoritma final kamu.
     */
    public static function levelFromScore(float $totalScore): string
    {
        return match (true) {
            $totalScore >= 67 => 'high',
            $totalScore >= 34 => 'medium',
            default => 'low',
        };
    }
}
