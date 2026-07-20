<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index']);
Route::get('/weather', [DashboardController::class, 'weather']);
Route::get('/currency', [DashboardController::class, 'currency']);
Route::get('/news', [DashboardController::class, 'news']);
Route::get('/ports', [DashboardController::class, 'ports']);
Route::get('/comparison', [DashboardController::class, 'comparison']);
Route::get('/watchlist', [DashboardController::class, 'watchlist']);
