<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    /**
     * GET /api/countries
     * GET /api/countries?search=germany
     * GET /api/countries?region=Asia
     */
    public function index(Request $request): JsonResponse
    {
        $query = Country::query()->with(['currency', 'riskScore']);

        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($region = $request->query('region')) {
            $query->where('region', $region);
        }

         // Ini digunakan oleh halaman comparison untuk dropdown 250+ negara
        if ($request->query('all') === 'true' || $request->query('limit') === 'all') {
            $countries = $query->orderBy('name')->get();
            return response()->json([
                'data' => CountryResource::collection($countries),
            ]);
        }

        $countries = $query->orderBy('name')->paginate(20);

        return response()->json([
            'data' => CountryResource::collection($countries),
            'meta' => [
                'current_page' => $countries->currentPage(),
                'last_page' => $countries->lastPage(),
                'total' => $countries->total(),
            ],
        ]);
    }

    /**
     * GET /api/countries/{idOrIsoCode}
     * Bisa dipanggil dengan id numerik atau iso_code (mis. "DEU").
     */
    public function show(string $idOrIsoCode): JsonResponse
    {
        $country = Country::with([
            'currency',
            'weatherSnapshot',
            'riskScore',
            'economicIndicators',
        ])
            ->where('id', $idOrIsoCode)
            ->orWhere('iso_code', strtoupper($idOrIsoCode))
            ->first();

        if (! $country) {
            return response()->json(['message' => 'Negara tidak ditemukan.'], 404);
        }

        return response()->json(['data' => new CountryResource($country)]);
    }
}