<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index']);
Route::get('/weather', [DashboardController::class, 'weather']);
Route::get('/currency', [DashboardController::class, 'currency']);
