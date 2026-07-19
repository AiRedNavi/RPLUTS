<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskScoreHistory extends Model
{
    use HasFactory;

    /**
     * Nama tabel sengaja singular ('risk_score_history', bukan
     * 'risk_score_histories') sesuai migration.
     */
    protected $table = 'risk_score_history';

    public $timestamps = false;

    protected $fillable = [
        'country_id',
        'total_score',
        'risk_level',
        'recorded_date',
    ];

    protected $casts = [
        'total_score' => 'decimal:2',
        'recorded_date' => 'date',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}