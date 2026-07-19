<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EconomicIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'year',
        'gdp',
        'inflation_rate',
        'population',
        'export_value',
        'import_value',
        'source',
    ];

    protected $casts = [
        'gdp' => 'decimal:2',
        'inflation_rate' => 'decimal:2',
        'population' => 'integer',
        'export_value' => 'decimal:2',
        'import_value' => 'decimal:2',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
