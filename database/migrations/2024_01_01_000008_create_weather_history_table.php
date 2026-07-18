<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')
                ->constrained('countries')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('rainfall', 6, 2)->nullable();
            $table->decimal('wind_speed', 6, 2)->nullable();
            $table->date('recorded_date');

            $table->unique(['country_id', 'recorded_date'], 'uq_weatherhist_country_date');
            $table->index('recorded_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_history');
    }
};
