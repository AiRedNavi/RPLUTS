<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('title', 300);
            $table->text('content');
            $table->foreignId('related_country_id')
                ->nullable()
                ->constrained('countries')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
