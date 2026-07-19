<?php

namespace App\Services;

use App\Models\NegativeWord;
use App\Models\NewsArticle;
use App\Models\PositiveWord;
use Illuminate\Support\Collection;

class SentimentAnalysisService
{
    protected Collection $positiveWords;

    protected Collection $negativeWords;

    public function __construct()
    {
        // Dictionary di-load sekali ke memori (bukan query berulang per
        // berita) supaya proses analisis banyak berita tidak lambat.
        $this->positiveWords = PositiveWord::pluck('word')
            ->map(fn ($word) => strtolower($word));

        $this->negativeWords = NegativeWord::pluck('word')
            ->map(fn ($word) => strtolower($word));
    }

    /**
     * Analisis semua berita yang belum punya sentiment_label, lalu
     * simpan hasilnya. Dipanggil dari Command: php artisan analyze:sentiment
     */
    public function analyzeAllPending(): int
    {
        $articles = NewsArticle::whereNull('sentiment_label')->get();
        $analyzed = 0;

        foreach ($articles as $article) {
            $this->analyzeArticle($article);
            $analyzed++;
        }

        return $analyzed;
    }

    /**
     * Analisis ulang SEMUA berita, termasuk yang sudah pernah dianalisis.
     * Berguna kalau dictionary kata sudah diperbarui dan mau di-refresh semua.
     */
    public function reanalyzeAll(): int
    {
        $articles = NewsArticle::all();
        $analyzed = 0;

        foreach ($articles as $article) {
            $this->analyzeArticle($article);
            $analyzed++;
        }

        return $analyzed;
    }

    /**
     * Analisis 1 berita: tokenisasi title + summary, cocokkan ke
     * dictionary positif/negatif, tentukan label, simpan ke database.
     *
     * Algoritma (sesuai spesifikasi proyek):
     *   Positive score > Negative score -> "positive"
     *   Negative score > Positive score -> "negative"
     *   Sama besar (termasuk 0 vs 0)     -> "neutral"
     */
    public function analyzeArticle(NewsArticle $article): void
    {
        $text = $article->title.' '.($article->summary ?? '');
        $words = $this->tokenize($text);

        $positiveScore = 0;
        $negativeScore = 0;

        foreach ($words as $word) {
            if ($this->positiveWords->contains($word)) {
                $positiveScore++;
            }

            if ($this->negativeWords->contains($word)) {
                $negativeScore++;
            }
        }

        $label = match (true) {
            $positiveScore > $negativeScore => 'positive',
            $negativeScore > $positiveScore => 'negative',
            default => 'neutral',
        };

        $article->setSentiment($label, $positiveScore, $negativeScore);
    }

    /**
     * Pecah teks jadi array kata: lowercase, buang tanda baca,
     * buang kata kosong/duplikat spasi.
     */
    protected function tokenize(string $text): array
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);
        $words = preg_split('/\s+/', trim($text));

        return array_filter($words, fn ($w) => $w !== '');
    }
}