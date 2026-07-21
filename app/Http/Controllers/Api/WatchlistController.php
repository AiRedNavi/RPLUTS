<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWatchlistRequest;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WatchlistController extends Controller
{
    public function index(Request $request)
    {
        $watchlists = Watchlist::with(['country.currency', 'country.riskScore'])
            ->where('user_id', Auth::id())
            ->latest('created_at')
            ->get();

        return response()->json([
            'data' => $watchlists->map(function (Watchlist $item) {
                $country = $item->country;

                return [
                    'id'         => $item->id,
                    'country_id' => $country->id,
                    'iso_code'   => $country->iso_code,
                    'name'       => $country->name,
                    'region'     => $country->region,
                    'currency'   => $country->currency ? [
                        'code'   => $country->currency->code,
                        'symbol' => $country->currency->symbol,
                    ] : null,
                    'risk_score' => $country->riskScore ? [
                        'total_score' => $country->riskScore->total_score,
                        'risk_level'  => $country->riskScore->risk_level,
                    ] : null,
                    'added_at'   => $item->created_at,
                ];
            }),
        ]);
    }

    public function store(StoreWatchlistRequest $request)
    {
        $exists = Watchlist::where('user_id', Auth::id())
            ->where('country_id', $request->validated('country_id'))
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Negara ini sudah ada di watchlist Anda.',
            ], 409);
        }

        $watchlist = Watchlist::create([
            'user_id'    => Auth::id(),
            'country_id' => $request->validated('country_id'),
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Berhasil ditambahkan ke watchlist.',
            'data'    => $watchlist,
        ], 201);
    }

    public function destroy(string $countryId)
    {
        $deleted = Watchlist::where('user_id', Auth::id())
            ->where('country_id', $countryId)
            ->delete();

        if (! $deleted) {
            return response()->json([
                'message' => 'Data watchlist tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Berhasil dihapus dari watchlist.',
        ]);
    }
}