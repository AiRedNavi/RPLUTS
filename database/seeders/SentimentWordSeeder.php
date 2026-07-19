<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SentimentWordSeeder extends Seeder
{
    /**
     * Dictionary untuk Lexicon Based Sentiment Analysis (lihat
     * SentimentAnalysisService). Daftar ini sengaja diperluas dari
     * contoh minimal di spesifikasi (yang cuma 5 kata) supaya deteksi
     * sentimen berita logistik/ekonomi/geopolitik lebih akurat.
     * Silakan ditambah lagi sesuai kebutuhan saat testing dengan
     * berita asli dari GNews API.
     */
    public function run(): void
    {
        $positiveWords = [
            'growth', 'increase', 'profit', 'stable', 'improve',
            'recovery', 'surplus', 'expansion', 'boost', 'gain',
            'rally', 'rebound', 'strengthen', 'upturn', 'thrive',
            'opportunity', 'efficient', 'breakthrough', 'agreement', 'cooperation',
            'investment', 'upgrade', 'resilient', 'optimistic', 'success',
            'partnership', 'innovation', 'productivity', 'confidence', 'progress',
        ];

        $negativeWords = [
            'war', 'crisis', 'inflation', 'delay', 'disaster',
            'decrease', 'conflict', 'shortage', 'recession', 'collapse',
            'decline', 'disruption', 'sanction', 'tariff', 'default',
            'bankruptcy', 'volatility', 'unrest', 'instability', 'downturn',
            'layoff', 'deficit', 'blockade', 'embargo', 'congestion',
            'strike', 'shutdown', 'tension', 'uncertainty', 'slowdown',
        ];

        foreach ($positiveWords as $word) {
            DB::table('positive_words')->updateOrInsert(['word' => $word]);
        }

        foreach ($negativeWords as $word) {
            DB::table('negative_words')->updateOrInsert(['word' => $word]);
        }
    }
}
