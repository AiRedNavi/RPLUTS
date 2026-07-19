<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComparisonController extends Controller
{
    /**
     * GET /api/comparison?countries=DEU,AUS
     * GET /api/comparison?countries=DEU,AUS,IDN,CHN (bisa lebih dari 2)
     *
     * Country Comparison Engine: bandingkan GDP, inflasi, risk, cuaca,
     * dan kurs beberapa negara sekaligus dalam 1 response.
     */
    public function compare(Request $request): JsonResponse
    {
        $isoCodes = collect(explode(',', (string) $request->query('countries', '')))
            ->map(fn ($code) => strtoupper(trim($code)))
            ->filter()
            ->values();

        if ($isoCodes->count() < 2) {
            return response()->json([
                'message' => 'Minimal 2 kode negara diperlukan, mis. ?countries=DEU,AUS',
            ], 422);
        }

        $countries = Country::with([
            'currency',
            'weatherSnapshot',
            'riskScore',
            'economicIndicators',
        ])
            ->whereIn('iso_code', $isoCodes)
            ->get();

        $comparison = $countries->map(function (Country $country) {
            $latestIndicator = $country->economicIndicators->sortByDesc('year')->first();

            return [
                'country' => [
                    'id' => $country->id,
                    'name' => $country->name,
                    'iso_code' => $country->iso_code,
                    'currency' => $country->currency?->code,
                ],
                'gdp' => $latestIndicator?->gdp,
                'inflation_rate' => $latestIndicator?->inflation_rate,
                'population' => $latestIndicator?->population,
                'weather' => $country->weatherSnapshot ? [
                    'temperature' => $country->weatherSnapshot->temperature,
                    'storm_risk_level' => $country->weatherSnapshot->storm_risk_level,
                ] : null,
                'risk_score' => $country->riskScore ? [
                    'total_score' => $country->riskScore->total_score,
                    'risk_level' => $country->riskScore->risk_level,
                ] : null,
            ];
        });

        return response()->json(['data' => $comparison]);
    }
}