<?php

namespace App\Console\Commands;

use App\Services\ExternalApi\WorldPortIndexService;
use Illuminate\Console\Command;

class FetchPortsData extends Command
{
    /**
     * php artisan fetch:ports
     * php artisan fetch:ports --file=storage/app/datasets/world_port_index.csv
     */
    protected $signature = 'fetch:ports {--file= : Path ke file CSV World Port Index}';

    protected $description = 'Import data pelabuhan dari dataset World Port Index (CSV statis)';

    public function handle(WorldPortIndexService $service): int
    {
        $filePath = $this->option('file')
            ?? storage_path('app/datasets/world_port_index.csv');

        if (! file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            $this->line('Unduh dataset dari https://msi.nga.mil/Publications/WPI lalu taruh di path tersebut,');
            $this->line('atau tentukan path lain dengan opsi --file=path/ke/file.csv');

            return self::FAILURE;
        }

        $this->info("Mengimpor data pelabuhan dari {$filePath}...");

        $imported = $service->importFromCsv($filePath);

        $this->info("Selesai. {$imported} pelabuhan berhasil diimpor.");

        return self::SUCCESS;
    }
}
