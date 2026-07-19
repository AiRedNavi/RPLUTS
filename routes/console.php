<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\DashboardController;

Schedule::command('fetch:weather')->everySixHours();
Schedule::command('fetch:exchange-rates')->everySixHours();
Schedule::command('fetch:news')->everySixHours();
Schedule::command('fetch:economic-indicators')->daily();
Schedule::command('fetch:countries')->weekly();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
