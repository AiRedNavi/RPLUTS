<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'iso_code',
        'region',
        'capital',
        'latitude',
        'longitude',
        'language',
        'currency_id',
    ];

    protected $casts = [
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function economicIndicators(): HasMany
    {
        return $this->hasMany(EconomicIndicator::class);
    }

    /**
     * Data cuaca terkini negara ini (1 baris per negara, di-update terus).
     */
    public function weatherSnapshot(): HasOne
    {
        return $this->hasOne(WeatherSnapshot::class);
    }

    public function weatherHistory(): HasMany
    {
        return $this->hasMany(WeatherHistory::class);
    }

    public function ports(): HasMany
    {
        return $this->hasMany(Port::class);
    }

    public function newsArticles(): HasMany
    {
        return $this->hasMany(NewsArticle::class);
    }

    /**
     * Skor risiko terkini negara ini (1 baris per negara, di-update terus).
     */
    public function riskScore(): HasOne
    {
        return $this->hasOne(RiskScore::class);
    }

    public function riskScoreHistory(): HasMany
    {
        return $this->hasMany(RiskScoreHistory::class);
    }

    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    /**
     * Artikel analisis admin yang terkait negara ini.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'related_country_id');
    }
}
