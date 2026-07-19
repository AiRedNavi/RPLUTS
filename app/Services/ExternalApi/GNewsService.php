<?php

namespace App\Services\ExternalApi;

use App\Models\ApiFetchLog;
use App\Models\Country;
use App\Models\NewsArticle;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GNewsService
{
    /**
     * Butuh API key gratis dari https://gnews.io/
     * Simpan di .env sebagai GNEWS_API_KEY, lalu daftarkan di
     * config/services.php:
     *   'gnews' => ['key' => env('GNEWS_API_KEY')],
     */
    protected function apiKey(): string
    {
        return config('services.gnews.key');
    }

    protected string $baseUrl = 'https://gnews.io/api/v4/search';

    /**
     * Kategori berita yang relevan untuk risk scoring, sesuai spesifikasi.
     * Query pencarian per kategori bisa disesuaikan lagi biar hasilnya
     * lebih relevan (mis. tambahkan nama negara di query).
     */
    protected array $categoryQueries = [
        'logistics' => 'supply chain logistics',
        'trade' => 'international trade',
        'shipping' => 'shipping port delay',
        'economy' => 'global economy inflation',
        'geopolitics' => 'geopolitical conflict trade',
    ];

    /**
     * Sinkronkan berita untuk semua kategori di atas.
     * Dipanggil dari Command: php artisan fetch:news
     *
     * Catatan: kolom sentiment_label sengaja dibiarkan null di sini.
     * Pengisiannya dilakukan oleh SentimentAnalysisService (Fase 3),
     * yang dipanggil setelah berita baru masuk.
     */
    public function syncAllCategories(): int
    {
        $synced = 0;

        foreach ($this->categoryQueries as $category => $query) {
            $result = $this->syncCategory($category, $query);

            if ($result === self::RATE_LIMITED) {
                Log::warning('GNewsService: berhenti sinkronisasi karena kena rate limit (429). Sisa kategori dilewati untuk hemat kuota.');
                break; // stop, jangan buang sisa kuota nyoba kategori lain yang pasti gagal juga
            }

            $synced += $result;

            // Jeda singkat antar kategori supaya tidak langsung kena
            // rate limit "request per detik" dari GNews.
            usleep(500000); // 0.5 detik
        }

        return $synced;
    }

    /**
     * Penanda khusus: dipakai syncAllCategories() untuk tahu kapan harus
     * berhenti lebih awal karena API sudah menolak request (429).
     */
    protected const RATE_LIMITED = -1;

    public function syncCategory(string $category, string $query): int
    {
        $startedAt = microtime(true);
        $synced = 0;

        try {
            $response = Http::timeout(30)->get($this->baseUrl, [
                'q' => $query,
                'lang' => 'en',
                'max' => 10,
                'token' => $this->apiKey(),
            ]);

            ApiFetchLog::record(
                'GNews API',
                '/search?q='.$query,
                $response->status(),
                (int) ((microtime(true) - $startedAt) * 1000)
            );

            if (! $response->successful()) {
                if ($response->status() === 429) {
                    Log::warning('GNewsService: kena rate limit (429) dari GNews API.', [
                        'category' => $category,
                    ]);

                    return self::RATE_LIMITED;
                }

                Log::warning('GNewsService: gagal fetch berita', [
                    'category' => $category,
                    'status' => $response->status(),
                ]);

                return 0;
            }

            $articles = $response->json('articles', []);

            foreach ($articles as $article) {
                if ($this->saveArticle($article, $category)) {
                    $synced++;
                }
            }
        } catch (Throwable $e) {
            Log::error('GNewsService error: '.$e->getMessage());
            ApiFetchLog::record('GNews API', '/search?q='.$query, null, null);
        }

        return $synced;
    }

    protected function saveArticle(array $article, string $category): bool
    {
        $title = $article['title'] ?? null;
        $url = $article['url'] ?? null;

        if (! $title || ! $url) {
            return false;
        }

        // Hindari duplikat berita yang sama persis (dicek dari source_url)
        $exists = NewsArticle::where('source_url', $url)->exists();
        if ($exists) {
            return false;
        }

        NewsArticle::create([
            'country_id' => $this->guessRelatedCountry($title, $article['description'] ?? ''),
            'title' => $title,
            'summary' => $article['description'] ?? null,
            'source_name' => $article['source']['name'] ?? null,
            'source_url' => $url,
            'category' => $category,
            'sentiment_label' => null, // diisi belakangan oleh SentimentAnalysisService
            'published_at' => $article['publishedAt'] ?? now(),
        ]);

        return true;
    }

    /**
     * Coba tebak negara terkait dari judul/deskripsi berita dengan
     * mencocokkan nama negara yang ada di database. Sederhana dan tidak
     * selalu akurat — silakan disempurnakan (mis. pakai NLP entity
     * extraction) kalau ada waktu lebih.
     */
    protected function guessRelatedCountry(string $title, string $description): ?int
    {
        $text = strtolower($title.' '.$description);

        $country = Country::all()->first(
            fn (Country $c) => str_contains($text, strtolower($c->name))
        );

        return $country?->id;
    }
}