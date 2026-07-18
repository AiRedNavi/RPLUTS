<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('iso_code', 3)->unique(); // IDN, DEU, CHN, dst
            $table->string('region', 100)->nullable();
            $table->string('capital', 150)->nullable();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->string('language', 100)->nullable();
            $table->foreignId('currency_id')
                ->nullable()
                ->constrained('currencies')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();

            $table->index('region');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
