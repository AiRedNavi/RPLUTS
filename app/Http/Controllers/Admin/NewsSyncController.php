<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

class NewsSyncController extends Controller
{
    /**
     * POST /admin/news/sync
     * Menjalankan fetch:news lalu analyze:sentiment secara berurutan,
     * dipicu manual oleh admin lewat tombol di UI (pengganti artisan CLI).
     */
    public function run(): JsonResponse
    {
        // Proses gabungan (fetch API eksternal + analisis) bisa memakan
        // waktu lebih dari batas default PHP di beberapa setup Laragon.
        set_time_limit(180);

        $log = [];

        try {
            $log[] = '→ Menjalankan fetch:news ...';
            $fetchExitCode = Artisan::call('fetch:news');
            $log[] = trim(Artisan::output()) ?: '(tidak ada output)';

            if ($fetchExitCode !== 0) {
                throw new \RuntimeException('fetch:news gagal dengan exit code ' . $fetchExitCode);
            }

            $log[] = '→ Menjalankan analyze:sentiment ...';
            $sentimentExitCode = Artisan::call('analyze:sentiment');
            $log[] = trim(Artisan::output()) ?: '(tidak ada output)';

            if ($sentimentExitCode !== 0) {
                throw new \RuntimeException('analyze:sentiment gagal dengan exit code ' . $sentimentExitCode);
            }

            return response()->json([
                'success' => true,
                'message' => 'Berita berhasil diambil dan dianalisis.',
                'log'     => $log,
            ]);
        } catch (Throwable $e) {
            Log::error('NewsSyncController::run failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menjalankan sinkronisasi berita: ' . $e->getMessage(),
                'log'     => $log,
            ], 500);
        }
    }
}