<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiFetchLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'api_name',
        'endpoint',
        'status_code',
        'response_time_ms',
        'fetched_at',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'response_time_ms' => 'integer',
        'fetched_at' => 'datetime',
    ];

    /**
     * Helper untuk dipanggil dari tiap Service setelah hit API eksternal.
     * Contoh: ApiFetchLog::record('GNews', '/search', 200, 340);
     */
    public static function record(string $apiName, ?string $endpoint, ?int $statusCode, ?int $responseTimeMs): self
    {
        return static::create([
            'api_name' => $apiName,
            'endpoint' => $endpoint,
            'status_code' => $statusCode,
            'response_time_ms' => $responseTimeMs,
            'fetched_at' => now(),
        ]);
    }
}
