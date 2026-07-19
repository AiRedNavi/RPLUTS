<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'symbol',
    ];

    /**
     * Negara-negara yang memakai mata uang ini.
     */
    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }

    /**
     * Kurs di mana currency ini jadi mata uang dasar (base).
     */
    public function ratesAsBase(): HasMany
    {
        return $this->hasMany(ExchangeRate::class, 'base_currency_id');
    }

    /**
     * Kurs di mana currency ini jadi mata uang tujuan (target).
     */
    public function ratesAsTarget(): HasMany
    {
        return $this->hasMany(ExchangeRate::class, 'target_currency_id');
    }
}
