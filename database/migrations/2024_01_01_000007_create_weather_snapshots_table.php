<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')
                ->constrained('countries')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('rainfall', 6, 2)->nullable();
            $table->decimal('wind_speed', 6, 2)->nullable();
            $table->enum('storm_risk_level', ['low', 'medium', 'high'])->default('low');
            $table->timestamp('fetched_at')->nullable()->useCurrent();

            $table->unique('country_id', 'uq_weathersnap_country');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_snapshots');
    }
};
