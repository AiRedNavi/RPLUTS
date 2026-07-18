<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watchlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('country_id')
                ->constrained('countries')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->unique(['user_id', 'country_id'], 'uq_watchlist_user_country');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchlists');
    }
};
