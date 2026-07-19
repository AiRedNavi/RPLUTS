<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use App\Models\Watchlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    /**
     * GET /api/watchlist
     * Daftar negara favorit milik user yang sedang login.
     *
     * CATATAN: route ini harus dipasangi middleware auth (lihat
     * routes/api.php), supaya $request->user() tidak null.
     */
    public function index(Request $request): JsonResponse
    {
        $countries = Country::with(['currency', 'riskScore'])
            ->whereHas('watchlists', fn ($q) => $q->where('user_id', $request->user()->id))
            ->get();

        return response()->json(['data' => CountryResource::collection($countries)]);
    }

    /**
     * POST /api/watchlist
     * Body: { "country_id": 5 }
     * Tambah negara ke watchlist user yang login.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country_id' => 'required|integer|exists:countries,id',
        ]);

        $watchlist = Watchlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'country_id' => $validated['country_id'],
        ]);

        return response()->json([
            'message' => 'Negara berhasil ditambahkan ke watchlist.',
            'data' => $watchlist,
        ], 201);
    }

    /**
     * DELETE /api/watchlist/{countryId}
     * Hapus negara dari watchlist user yang login.
     */
    public function destroy(Request $request, int $countryId): JsonResponse
    {
        $deleted = Watchlist::where('user_id', $request->user()->id)
            ->where('country_id', $countryId)
            ->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Negara ini tidak ada di watchlist kamu.'], 404);
        }

        return response()->json(['message' => 'Negara berhasil dihapus dari watchlist.']);
    }
}