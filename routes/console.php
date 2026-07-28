<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('fetch:weather')->everySixHours();
Schedule::command('fetch:exchange-rates')->everySixHours();
Schedule::command('fetch:news')->everySixHours();
Schedule::command('fetch:countries')->daily();
Schedule::command('fetch:economic-indicators')->daily();
Schedule::command('analyze:sentiment', ['--all' => true])->daily();
Schedule::command('risk:calculate')->daily();
Schedule::command('fetch:ports', ['--file' => storage_path('app/datasets/World_Port_Index.csv')])->weekly();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
