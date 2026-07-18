<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_fetch_logs', function (Blueprint $table) {
            $table->id();
            $table->string('api_name', 100); // 'Open-Meteo', 'GNews', dst
            $table->string('endpoint', 500)->nullable();
            $table->integer('status_code')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->timestamp('fetched_at')->nullable()->useCurrent();

            $table->index('api_name');
            $table->index('fetched_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_fetch_logs');
    }
};
