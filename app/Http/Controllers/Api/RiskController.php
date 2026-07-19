<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RiskScoreResource;
use App\Models\Country;
use App\Models\RiskScore;
use App\Models\RiskScoreHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RiskController extends Controller
{
    /**
     * GET /api/risk
     * GET /api/risk?level=high
     * GET /api/risk?sort=total_score&direction=desc
     */
    public function index(Request $request): JsonResponse
    {
        $query = RiskScore::query()->with('country');

        if ($level = $request->query('level')) {
            $query->where('risk_level', $level);
        }

        $sort = $request->query('sort', 'total_score');
        $direction = $request->query('direction', 'desc');
        $query->orderBy($sort, $direction);

        $riskScores = $query->paginate(20);

        return response()->json([
            'data' => RiskScoreResource::collection($riskScores),
            'meta' => [
                'current_page' => $riskScores->currentPage(),
                'last_page' => $riskScores->lastPage(),
                'total' => $riskScores->total(),
            ],
        ]);
    }

    /**
     * GET /api/risk/{idOrIsoCode}
     * Detail risk score 1 negara, termasuk breakdown 4 komponen.
     */
    public function show(string $idOrIsoCode): JsonResponse
    {
        $country = Country::where('id', $idOrIsoCode)
            ->orWhere('iso_code', strtoupper($idOrIsoCode))
            ->first();

        if (! $country) {
            return response()->json(['message' => 'Negara tidak ditemukan.'], 404);
        }

        $riskScore = RiskScore::with('country')->where('country_id', $country->id)->first();

        if (! $riskScore) {
            return response()->json(['message' => 'Risk score belum dihitung untuk negara ini. Jalankan risk:calculate.'], 404);
        }

        return response()->json(['data' => new RiskScoreResource($riskScore)]);
    }

    /**
     * GET /api/risk/{idOrIsoCode}/history
     * Histori risk score untuk grafik tren (dipakai di Data Visualization Dashboard).
     */
    public function history(string $idOrIsoCode): JsonResponse
    {
        $country = Country::where('id', $idOrIsoCode)
            ->orWhere('iso_code', strtoupper($idOrIsoCode))
            ->first();

        if (! $country) {
            return response()->json(['message' => 'Negara tidak ditemukan.'], 404);
        }

        $history = RiskScoreHistory::where('country_id', $country->id)
            ->orderBy('recorded_date')
            ->get(['total_score', 'risk_level', 'recorded_date']);

        return response()->json(['data' => $history]);
    }
}