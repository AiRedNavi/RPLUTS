<?php

namespace App\Services\ExternalApi;

use App\Models\ApiFetchLog;
use App\Models\Country;
use App\Models\Port;
use Illuminate\Support\Facades\Log;
use Throwable;

class WorldPortIndexService
{
    /**
     * World Port Index BUKAN REST API real-time, melainkan dataset
     * statis (CSV/JSON) yang bisa diunduh dari:
     * https://msi.nga.mil/Publications/WPI
     *
     * Alurnya: unduh file sekali secara manual, taruh di
     * storage/app/datasets/world_port_index.csv, lalu jalankan
     * import ini sekali (atau setiap kali dataset di-update).
     *
     * Dipanggil dari Command: php artisan fetch:ports
     */
    public function importFromCsv(string $filePath): int
    {
        $imported = 0;

        if (! file_exists($filePath)) {
            Log::error("WorldPortIndexService: file tidak ditemukan di {$filePath}");

            return 0;
        }

        try {
            $handle = fopen($filePath, 'r');
            $header = fgetcsv($handle); // baris pertama = nama kolom

            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($header, $row);

                if ($this->importSingleRow($data)) {
                    $imported++;
                }
            }

            fclose($handle);

            ApiFetchLog::record('World Port Index Dataset', $filePath, 200, null);
        } catch (Throwable $e) {
            Log::error('WorldPortIndexService error: '.$e->getMessage());
            ApiFetchLog::record('World Port Index Dataset', $filePath, null, null);
        }

        return $imported;
    }

    /**
     * Sesuaikan nama kolom ($data['...']) dengan header asli file CSV
     * yang kamu unduh — nama kolom di dataset asli biasanya:
     * 'Main Port Name', 'Country Code', 'Latitude', 'Longitude', 'World Port Index Number'.
     */
    protected function importSingleRow(array $data): bool
    {
        $portName = $data['Main Port Name'] ?? null;
        $countryCode = $data['Country Code'] ?? null; // biasanya ISO 2 huruf, perlu mapping ke iso_code (3 huruf) kita
        $latitude = $data['Latitude'] ?? null;
        $longitude = $data['Longitude'] ?? null;
        $unlocode = $data['World Port Index Number'] ?? null;

        if (! $portName || ! $countryCode) {
            return false;
        }

        // Catatan: kalau dataset pakai ISO 2 huruf sedangkan tabel countries
        // pakai ISO 3 huruf (cca3), butuh tabel mapping tambahan atau
        // library konversi kode negara. Untuk contoh ini diasumsikan
        // sudah cocok dengan kolom iso_code.
        $country = Country::where('iso_code', $countryCode)->first();

        if (! $country) {
            return false;
        }

        Port::updateOrCreate(
            ['name' => $portName, 'country_id' => $country->id],
            [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'unlocode' => $unlocode,
            ]
        );

        return true;
    }
}
