<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

class CurrencySyncController extends Controller
{
    /**
     * POST /currency/sync
     * Menjalankan fetch:exchange-rates, dipicu manual oleh user yang
     * sudah login lewat tombol di halaman Currency Impact Dashboard.
     * Bukan endpoint admin — middleware-nya cukup 'auth' biasa.
     */
    public function run(): JsonResponse
    {
        set_time_limit(120);

        try {
            $exitCode = Artisan::call('fetch:exchange-rates');
            $output = trim(Artisan::output()) ?: '(tidak ada output)';

            if ($exitCode !== 0) {
                throw new \RuntimeException('fetch:exchange-rates gagal dengan exit code ' . $exitCode);
            }

            return response()->json([
                'success' => true,
                'message' => 'Kurs berhasil diperbarui.',
                'log'     => $output,
            ]);
        } catch (Throwable $e) {
            Log::error('CurrencySyncController::run failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui kurs: ' . $e->getMessage(),
            ], 500);
        }
    }
}