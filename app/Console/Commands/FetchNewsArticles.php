<?php

namespace App\Console\Commands;

use App\Services\ExternalApi\GNewsService;
use Illuminate\Console\Command;

class FetchNewsArticles extends Command
{
    /**
     * php artisan fetch:news
     */
    protected $signature = 'fetch:news';

    protected $description = 'Ambil berita logistik/trade/shipping/economy/geopolitics dari GNews API';

    public function handle(GNewsService $service): int
    {
        $this->info('Mengambil berita dari GNews API...');

        $synced = $service->syncAllCategories();

        $this->info("Selesai. {$synced} berita baru berhasil disimpan.");
        $this->comment('Catatan: sentimen berita belum dianalisis di sini.');
        $this->comment('Jalankan php artisan analyze:sentiment setelah ini (dibuat di Fase 3).');

        return self::SUCCESS;
    }
}
