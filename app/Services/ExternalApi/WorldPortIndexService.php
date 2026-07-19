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
     * Mapping ISO 2-letter code → ISO 3-letter code
     * (File CSV World Port Index pakai ISO2, database kita pakai ISO3)
     * Sumber: https://en.wikipedia.org/wiki/ISO_3166-1
     * Disini hanya ISO2→ISO3 untuk negara utama yang ada di dunia.
     */
    protected array $iso2ToIso3 = [
        'AD' => 'AND', 'AE' => 'ARE', 'AF' => 'AFG', 'AG' => 'ATG', 'AI' => 'AIA',
        'AL' => 'ALB', 'AM' => 'ARM', 'AO' => 'AGO', 'AQ' => 'ATA', 'AR' => 'ARG',
        'AS' => 'ASM', 'AT' => 'AUT', 'AU' => 'AUS', 'AW' => 'ABW', 'AX' => 'ALA',
        'AZ' => 'AZE', 'BA' => 'BIH', 'BB' => 'BRB', 'BD' => 'BGD', 'BE' => 'BEL',
        'BF' => 'BFA', 'BG' => 'BGR', 'BH' => 'BHR', 'BI' => 'BDI', 'BJ' => 'BEN',
        'BL' => 'BLM', 'BM' => 'BMU', 'BN' => 'BRN', 'BO' => 'BOL', 'BQ' => 'BES',
        'BR' => 'BRA', 'BS' => 'BHS', 'BT' => 'BTN', 'BV' => 'BVT', 'BW' => 'BWA',
        'BY' => 'BLR', 'BZ' => 'BLZ', 'CA' => 'CAN', 'CC' => 'CCK', 'CD' => 'COD',
        'CF' => 'CAF', 'CG' => 'COG', 'CH' => 'CHE', 'CI' => 'CIV', 'CK' => 'COK',
        'CL' => 'CHL', 'CM' => 'CMR', 'CN' => 'CHN', 'CO' => 'COL', 'CR' => 'CRI',
        'CU' => 'CUB', 'CV' => 'CPV', 'CW' => 'CUW', 'CX' => 'CXR', 'CY' => 'CYP',
        'CZ' => 'CZE', 'DE' => 'DEU', 'DJ' => 'DJI', 'DK' => 'DNK', 'DM' => 'DMA',
        'DO' => 'DOM', 'DZ' => 'DZA', 'EC' => 'ECU', 'EE' => 'EST', 'EG' => 'EGY',
        'EH' => 'ESH', 'ER' => 'ERI', 'ES' => 'ESP', 'ET' => 'ETH', 'FI' => 'FIN',
        'FJ' => 'FJI', 'FK' => 'FLK', 'FM' => 'FSM', 'FO' => 'FRO', 'FR' => 'FRA',
        'GA' => 'GAB', 'GB' => 'GBR', 'GD' => 'GRD', 'GE' => 'GEO', 'GF' => 'GUF',
        'GG' => 'GGY', 'GH' => 'GHA', 'GI' => 'GIB', 'GL' => 'GRL', 'GM' => 'GMB',
        'GN' => 'GIN', 'GP' => 'GLP', 'GQ' => 'GNQ', 'GR' => 'GRC', 'GS' => 'SGS',
        'GT' => 'GTM', 'GU' => 'GUM', 'GW' => 'GNB', 'GY' => 'GUY', 'HK' => 'HKG',
        'HM' => 'HMD', 'HN' => 'HND', 'HR' => 'HRV', 'HT' => 'HTI', 'HU' => 'HUN',
        'ID' => 'IDN', 'IE' => 'IRL', 'IL' => 'ISR', 'IM' => 'IMN', 'IN' => 'IND',
        'IO' => 'IOT', 'IQ' => 'IRQ', 'IR' => 'IRN', 'IS' => 'ISL', 'IT' => 'ITA',
        'JE' => 'JEY', 'JM' => 'JAM', 'JO' => 'JOR', 'JP' => 'JPN', 'KE' => 'KEN',
        'KG' => 'KGZ', 'KH' => 'KHM', 'KI' => 'KIR', 'KM' => 'COM', 'KN' => 'KNA',
        'KP' => 'PRK', 'KR' => 'KOR', 'KW' => 'KWT', 'KY' => 'CYM', 'KZ' => 'KAZ',
        'LA' => 'LAO', 'LB' => 'LBN', 'LC' => 'LCA', 'LI' => 'LIE', 'LK' => 'LKA',
        'LR' => 'LBR', 'LS' => 'LSO', 'LT' => 'LTU', 'LU' => 'LUX', 'LV' => 'LVA',
        'LY' => 'LBY', 'MA' => 'MAR', 'MC' => 'MCO', 'MD' => 'MDA', 'ME' => 'MNE',
        'MF' => 'MAF', 'MG' => 'MDG', 'MH' => 'MHL', 'MK' => 'MKD', 'ML' => 'MLI',
        'MM' => 'MMR', 'MN' => 'MNG', 'MO' => 'MAC', 'MP' => 'MNP', 'MQ' => 'MTQ',
        'MR' => 'MRT', 'MS' => 'MSR', 'MT' => 'MLT', 'MU' => 'MUS', 'MV' => 'MDV',
        'MW' => 'MWI', 'MX' => 'MEX', 'MY' => 'MYS', 'MZ' => 'MOZ', 'NA' => 'NAM',
        'NC' => 'NCL', 'NE' => 'NER', 'NF' => 'NFK', 'NG' => 'NGA', 'NI' => 'NIC',
        'NL' => 'NLD', 'NO' => 'NOR', 'NP' => 'NPL', 'NR' => 'NRU', 'NU' => 'NIU',
        'NZ' => 'NZL', 'OM' => 'OMN', 'PA' => 'PAN', 'PE' => 'PER', 'PF' => 'PYF',
        'PG' => 'PNG', 'PH' => 'PHL', 'PK' => 'PAK', 'PL' => 'POL', 'PM' => 'SPM',
        'PN' => 'PCN', 'PR' => 'PRI', 'PS' => 'PSE', 'PT' => 'PRT', 'PW' => 'PLW',
        'PY' => 'PRY', 'QA' => 'QAT', 'RE' => 'REU', 'RO' => 'ROU', 'RS' => 'SRB',
        'RU' => 'RUS', 'RW' => 'RWA', 'SA' => 'SAU', 'SB' => 'SLB', 'SC' => 'SYC',
        'SD' => 'SDN', 'SE' => 'SWE', 'SG' => 'SGP', 'SH' => 'SHN', 'SI' => 'SVN',
        'SJ' => 'SJM', 'SK' => 'SVK', 'SL' => 'SLE', 'SM' => 'SMR', 'SN' => 'SEN',
        'SO' => 'SOM', 'SR' => 'SUR', 'SS' => 'SSD', 'ST' => 'STP', 'SV' => 'SLV',
        'SX' => 'SXM', 'SY' => 'SYR', 'SZ' => 'SWZ', 'TC' => 'TCA', 'TD' => 'TCD',
        'TF' => 'ATF', 'TG' => 'TGO', 'TH' => 'THA', 'TJ' => 'TJK', 'TK' => 'TKL',
        'TL' => 'TLS', 'TM' => 'TKM', 'TN' => 'TUN', 'TO' => 'TON', 'TR' => 'TUR',
        'TT' => 'TTO', 'TV' => 'TUV', 'TW' => 'TWN', 'TZ' => 'TZA', 'UA' => 'UKR',
        'UG' => 'UGA', 'UM' => 'UMI', 'US' => 'USA', 'UY' => 'URY', 'UZ' => 'UZB',
        'VA' => 'VAT', 'VC' => 'VCT', 'VE' => 'VEN', 'VG' => 'VGB', 'VI' => 'VIR',
        'VN' => 'VNM', 'VU' => 'VUT', 'WF' => 'WLF', 'WS' => 'WSM', 'YE' => 'YEM',
        'YT' => 'MYT', 'ZA' => 'ZAF', 'ZM' => 'ZMB', 'ZW' => 'ZWE',
    ];

    /**
     * Import data pelabuhan dari file CSV World Port Index.
     * File diunduh manual dari https://arc-gis-hub-home-arcgishub.hub.arcgis.com/datasets/EDT::world-port-index
     *
     * Dipanggil dari Command: php artisan fetch:ports
     */
    public function importFromCsv(string $filePath): int
    {
        $imported = 0;

        if (!file_exists($filePath)) {
            Log::error("WorldPortIndexService: file tidak ditemukan di {$filePath}");
            return 0;
        }

        try {
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                throw new \Exception("Tidak bisa membuka file: {$filePath}");
            }

            // Baris pertama = header
            $header = fgetcsv($handle);
            if (!$header) {
                throw new \Exception("File CSV kosong atau tidak valid");
            }

            // Buat mapping kolom (nama kolom => index)
            $columnMap = array_flip($header);

            while (($row = fgetcsv($handle)) !== false) {
                // Rekombinasi baris dengan header jadi associative array
                $data = array_combine($header, array_pad($row, count($header), null));

                if ($this->importSingleRow($data)) {
                    $imported++;
                }
            }

            fclose($handle);

            ApiFetchLog::record('World Port Index CSV', $filePath, 200, null);
        } catch (Throwable $e) {
            Log::error('WorldPortIndexService error: '.$e->getMessage());
            ApiFetchLog::record('World Port Index CSV', $filePath, null, null);
        }

        return $imported;
    }

    /**
     * Simpan satu baris data pelabuhan dari CSV ke database.
     * Kolom yang dipakai: PORT_NAME, COUNTRY (ISO2), LATITUDE, LONGITUDE, INDEX_NO
     */
    protected function importSingleRow(array $data): bool
    {
        $portName = trim($data['PORT_NAME'] ?? '');
        $countryCode2 = strtoupper(trim($data['COUNTRY'] ?? ''));
        $latitude = $data['LATITUDE'] ?? null;
        $longitude = $data['LONGITUDE'] ?? null;
        $unlocode = $data['INDEX_NO'] ?? null;

        if (!$portName || !$countryCode2) {
            return false;
        }

        // Konversi ISO2 ke ISO3
        $countryCode3 = $this->iso2ToIso3[$countryCode2] ?? null;

        if (!$countryCode3) {
            Log::debug("WorldPortIndexService: ISO2 code '{$countryCode2}' tidak diketahui (port: {$portName})");
            return false;
        }

        // Cari negara di database berdasarkan ISO3
        $country = Country::where('iso_code', $countryCode3)->first();

        if (!$country) {
            Log::debug("WorldPortIndexService: Negara dengan ISO3 '{$countryCode3}' tidak ada di database (port: {$portName})");
            return false;
        }

        // Coba cari port yang sudah ada (untuk hindari duplikat)
        $existing = Port::where('name', $portName)
            ->where('country_id', $country->id)
            ->first();

        if ($existing) {
            // Update kalau sudah ada
            $existing->update([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'unlocode' => $unlocode,
            ]);
        } else {
            // Insert kalau belum ada
            Port::create([
                'name' => $portName,
                'country_id' => $country->id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'unlocode' => $unlocode,
            ]);
        }

        return true;
    }
}