<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRateHistory extends Model
{
    use HasFactory;

    /**
     * Nama tabel sengaja singular ('exchange_rate_history', bukan
     * 'exchange_rate_histories') sesuai migration.
     */
    protected $table = 'exchange_rate_history';

    public $timestamps = false;

    protected $fillable = [
        'base_currency_id',
        'target_currency_id',
        'rate',
        'recorded_date',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'recorded_date' => 'date',
    ];

    public function baseCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency_id');
    }

    public function targetCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'target_currency_id');
    }
}