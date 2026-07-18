<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_score_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')
                ->constrained('countries')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->decimal('total_score', 5, 2);
            $table->enum('risk_level', ['low', 'medium', 'high']);
            $table->date('recorded_date');

            $table->unique(['country_id', 'recorded_date'], 'uq_riskhist_country_date');
            $table->index('recorded_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_score_history');
    }
};
