<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExchangeRateResource;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\ExchangeRateHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * GET /api/currency
     * GET /api/currency?base=USD
     * Daftar kurs terkini dari 1 mata uang basis ke semua mata uang lain.
     */
    public function index(Request $request): JsonResponse
    {
        $baseCode = strtoupper($request->query('base', 'USD'));

        $baseCurrency = Currency::where('code', $baseCode)->first();

        if (! $baseCurrency) {
            return response()->json(['message' => "Mata uang basis {$baseCode} tidak ditemukan."], 404);
        }

        $rates = ExchangeRate::with(['baseCurrency', 'targetCurrency'])
            ->where('base_currency_id', $baseCurrency->id)
            ->get();

        return response()->json(['data' => ExchangeRateResource::collection($rates)]);
    }

    /**
     * GET /api/currency/history?base=USD&target=IDR
     * Histori kurs untuk grafik tren (dipakai Currency Impact Dashboard).
     */
    public function history(Request $request): JsonResponse
    {
        $baseCode = strtoupper($request->query('base', 'USD'));
        $targetCode = strtoupper($request->query('target', ''));

        $baseCurrency = Currency::where('code', $baseCode)->first();
        $targetCurrency = Currency::where('code', $targetCode)->first();

        if (! $baseCurrency || ! $targetCurrency) {
            return response()->json(['message' => 'Mata uang basis atau target tidak ditemukan.'], 404);
        }

        $history = ExchangeRateHistory::where('base_currency_id', $baseCurrency->id)
            ->where('target_currency_id', $targetCurrency->id)
            ->orderBy('recorded_date')
            ->get(['rate', 'recorded_date']);

        return response()->json(['data' => $history]);
    }
}