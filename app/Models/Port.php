<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Port extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country_id',
        'latitude',
        'longitude',
        'unlocode',
    ];

    protected $casts = [
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
