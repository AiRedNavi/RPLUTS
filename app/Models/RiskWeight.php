<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskWeight extends Model
{
    use HasFactory;

    protected $fillable = [
        'weather_weight',
        'inflation_weight',
        'news_weight',
        'currency_weight',
        'is_active',
    ];

    protected $casts = [
        'weather_weight' => 'decimal:2',
        'inflation_weight' => 'decimal:2',
        'news_weight' => 'decimal:2',
        'currency_weight' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Ambil konfigurasi bobot yang sedang aktif.
     * Dipakai oleh RiskScoringService saat menghitung skor.
     */
    public static function active(): ?self
    {
        return static::where('is_active', true)->latest('id')->first();
    }
}
