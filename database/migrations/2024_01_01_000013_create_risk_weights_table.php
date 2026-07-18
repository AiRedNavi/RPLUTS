<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_weights', function (Blueprint $table) {
            $table->id();
            $table->decimal('weather_weight', 5, 2)->default(30.00);
            $table->decimal('inflation_weight', 5, 2)->default(20.00);
            $table->decimal('news_weight', 5, 2)->default(40.00);
            $table->decimal('currency_weight', 5, 2)->default(10.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_weights');
    }
};
