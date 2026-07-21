<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWatchlistRequest;
use App\Models\Watchlist;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    public function index(Request $request)
    {
        $watchlists = $request->user()
            ->watchlists()
            ->with(['country.currency', 'country.riskScores' => function ($q) {
                $q->latest('calculated_at')->limit(1);
            }])
            ->latest('created_at')
            ->get();

        return response()->json([
            'data' => $watchlists->map(function ($item) {
                $risk = $item->country->riskScores->first();

                return [
                    'watchlist_id' => $item->id,
                    'country_id' => $item->country->id,
                    'country_name' => $item->country->name,
                    'iso_code' => $item->country->iso_code,
                    'risk_score' => $risk?->total_score,
                    'risk_level' => $risk?->risk_level,
                    'added_at' => $item->created_at,
                ];
            }),
        ]);
    }

    public function store(StoreWatchlistRequest $request)
    {
        $exists = $request->user()
            ->watchlists()
            ->where('country_id', $request->country_id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Negara ini sudah ada di watchlist kamu.'], 409);
        }

        $watchlist = $request->user()->watchlists()->create([
            'country_id' => $request->country_id,
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'Berhasil ditambahkan ke watchlist.', 'data' => $watchlist], 201);
    }

    public function destroy(Request $request, int $countryId)
    {
        $deleted = $request->user()
            ->watchlists()
            ->where('country_id', $countryId)
            ->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Data watchlist tidak ditemukan.'], 404);
        }

        return response()->json(['message' => 'Berhasil dihapus dari watchlist.']);
    }
}