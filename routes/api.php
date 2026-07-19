<?php

use App\Http\Controllers\Api\ComparisonController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\PortController;
use App\Http\Controllers\Api\RiskController;
use App\Http\Controllers\Api\WatchlistController;
use App\Http\Controllers\Api\WeatherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Global Supply Chain Risk Intelligence Platform
|--------------------------------------------------------------------------
| Semua route di sini otomatis punya prefix /api (diatur di
| bootstrap/app.php bawaan Laravel). Total endpoint saat ini: 16,
| akan bertambah lagi seiring fitur admin dibuat di Fase 6.
*/

// ------------------------------------------------------------------
// Countries
// ------------------------------------------------------------------
Route::get('/countries', [CountryController::class, 'index']);
Route::get('/countries/{idOrIsoCode}', [CountryController::class, 'show']);

// ------------------------------------------------------------------
// Risk Scoring
// ------------------------------------------------------------------
Route::get('/risk', [RiskController::class, 'index']);
Route::get('/risk/{idOrIsoCode}', [RiskController::class, 'show']);
Route::get('/risk/{idOrIsoCode}/history', [RiskController::class, 'history']);

// ------------------------------------------------------------------
// Ports
// ------------------------------------------------------------------
Route::get('/ports', [PortController::class, 'index']);
Route::get('/ports/{id}', [PortController::class, 'show']);

// ------------------------------------------------------------------
// News
// ------------------------------------------------------------------
Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{id}', [NewsController::class, 'show']);

// ------------------------------------------------------------------
// Currency
// ------------------------------------------------------------------
Route::get('/currency', [CurrencyController::class, 'index']);
Route::get('/currency/history', [CurrencyController::class, 'history']);

// ------------------------------------------------------------------
// Weather
// ------------------------------------------------------------------
Route::get('/weather', [WeatherController::class, 'index']);
Route::get('/weather/{idOrIsoCode}/history', [WeatherController::class, 'history']);

// ------------------------------------------------------------------
// Country Comparison Engine
// ------------------------------------------------------------------
Route::get('/comparison', [ComparisonController::class, 'compare']);

// ------------------------------------------------------------------
// Watchlist (butuh login — sesuaikan middleware auth:sanctum kalau
// pakai Laravel Sanctum, atau auth:api / middleware auth sesuai
// setup AuthController yang dibuat di Fase 6)
// ------------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/watchlist', [WatchlistController::class, 'index']);
    Route::post('/watchlist', [WatchlistController::class, 'store']);
    Route::delete('/watchlist/{countryId}', [WatchlistController::class, 'destroy']);
});