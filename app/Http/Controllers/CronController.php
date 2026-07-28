<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CronController extends Controller
{
    /**
     * Endpoint ini dipanggil oleh layanan cron eksternal gratis (mis. cron-job.org)
     * secara berkala (misalnya tiap 6 jam) untuk menjalankan semua scheduled command
     * Laravel, karena Railway free tier tidak menyediakan cron job bawaan.
     *
     * Route contoh (tambahkan di routes/web.php atau routes/api.php):
     * Route::get('/cron/run-schedule', [CronController::class, 'runSchedule']);
     *
     * Amankan endpoint ini dengan secret token via query string, contoh:
     * https://nama-app-kamu.up.railway.app/cron/run-schedule?token=RAHASIA_KAMU
     *
     * Simpan token di .env sebagai CRON_SECRET_TOKEN, lalu tambahkan di
     * cron-job.org sebagai bagian dari URL yang dipanggil.
     */
    public function runSchedule(Request $request)
    {
        $expectedToken = env('CRON_SECRET_TOKEN');

        if (!$expectedToken || $request->query('token') !== $expectedToken) {
            abort(403, 'Invalid or missing token.');
        }

        // Menjalankan semua command yang terdaftar di jadwal (Kernel/routes/console.php)
        Artisan::call('schedule:run');

        return response()->json([
            'status' => 'ok',
            'ran_at' => now()->toDateTimeString(),
            'output' => Artisan::output(),
        ]);
    }

    /**
     * Alternatif: jalankan satu command spesifik langsung, kalau kamu mau
     * kontrol lebih granular per command lewat cron eksternal yang terpisah-pisah.
     * Contoh: /cron/run-command?token=...&command=app:fetch-weather-data
     */
    public function runSingleCommand(Request $request)
    {
        $expectedToken = env('CRON_SECRET_TOKEN');

        if (!$expectedToken || $request->query('token') !== $expectedToken) {
            abort(403, 'Invalid or missing token.');
        }

        $allowedCommands = [
            'app:fetch-countries',
            'app:fetch-economic-indicators',
            'app:fetch-weather',
            'app:fetch-exchange-rates',
            'app:fetch-news',
            'app:risk-calculate',
            'app:fetch-ports',
            'app:analyze-sentiment',
        ];

        $command = $request->query('command');

        if (!in_array($command, $allowedCommands, true)) {
            abort(400, 'Command not allowed.');
        }

        Artisan::call($command);

        return response()->json([
            'status' => 'ok',
            'command' => $command,
            'output' => Artisan::output(),
        ]);
    }
}
