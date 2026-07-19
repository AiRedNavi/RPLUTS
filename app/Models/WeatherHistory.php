<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherHistory extends Model
{
    use HasFactory;

    /**
     * Nama tabel sengaja singular ('weather_history', bukan
     * 'weather_histories') sesuai migration. Eloquent secara default
     * akan menebak nama plural, jadi harus di-set manual di sini.
     */
    protected $table = 'weather_history';

    public $timestamps = false;

    protected $fillable = [
        'country_id',
        'temperature',
        'rainfall',
        'wind_speed',
        'recorded_date',
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'rainfall' => 'decimal:2',
        'wind_speed' => 'decimal:2',
        'recorded_date' => 'date',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}