<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('economic_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')
                ->constrained('countries')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->unsignedSmallInteger('year');
            $table->decimal('gdp', 20, 2)->nullable();
            $table->decimal('inflation_rate', 6, 2)->nullable();
            $table->unsignedBigInteger('population')->nullable();
            $table->decimal('export_value', 20, 2)->nullable();
            $table->decimal('import_value', 20, 2)->nullable();
            $table->string('source', 100)->default('World Bank API');
            $table->timestamps();

            $table->unique(['country_id', 'year'], 'uq_econ_country_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('economic_indicators');
    }
};
