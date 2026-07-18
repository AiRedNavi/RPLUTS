<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rate_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('base_currency_id')
                ->constrained('currencies')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('target_currency_id')
                ->constrained('currencies')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->decimal('rate', 18, 6);
            $table->date('recorded_date');

            $table->unique(
                ['base_currency_id', 'target_currency_id', 'recorded_date'],
                'uq_exhist_pair_date'
            );
            $table->index('recorded_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rate_history');
    }
};
