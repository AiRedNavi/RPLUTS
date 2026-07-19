<?php

namespace App\Console\Commands;

use App\Services\SentimentAnalysisService;
use Illuminate\Console\Command;

class AnalyzeSentiment extends Command
{
    /**
     * php artisan analyze:sentiment
     * php artisan analyze:sentiment --all   (analisis ulang SEMUA berita, bukan cuma yang baru)
     */
    protected $signature = 'analyze:sentiment {--all : Analisis ulang semua berita, termasuk yang sudah dianalisis}';

    protected $description = 'Analisis sentimen berita (lexicon-based) untuk berita yang belum diberi label';

    public function handle(SentimentAnalysisService $service): int
    {
        if ($this->option('all')) {
            $this->info('Menganalisis ulang SEMUA berita...');
            $analyzed = $service->reanalyzeAll();
        } else {
            $this->info('Menganalisis berita yang belum punya label sentimen...');
            $analyzed = $service->analyzeAllPending();
        }

        $this->info("Selesai. {$analyzed} berita berhasil dianalisis.");

        return self::SUCCESS;
    }
}
