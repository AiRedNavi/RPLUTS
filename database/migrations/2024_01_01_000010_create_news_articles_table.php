<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')
                ->nullable() // berita bisa terkait 1 negara atau global
                ->constrained('countries')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->string('title', 300);
            $table->text('summary')->nullable();
            $table->string('source_name', 150)->nullable();
            $table->string('source_url', 500)->nullable();
            $table->enum('category', [
                'logistics', 'trade', 'shipping', 'economy', 'geopolitics',
            ])->default('economy');
            $table->enum('sentiment_label', ['positive', 'neutral', 'negative'])->nullable();
            $table->unsignedInteger('positive_score')->default(0);
            $table->unsignedInteger('negative_score')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index('category');
            $table->index('sentiment_label');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_articles');
    }
};
