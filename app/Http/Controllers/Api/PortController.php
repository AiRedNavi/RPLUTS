<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PortResource;
use App\Models\Port;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortController extends Controller
{
    /**
     * GET /api/ports
     * GET /api/ports?search=rotterdam
     * GET /api/ports?country=NLD  (filter by iso_code negara)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Port::query()->with('country');

        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($countryIso = $request->query('country')) {
            $query->whereHas('country', fn ($q) => $q->where('iso_code', strtoupper($countryIso)));
        }

        // Get all ports (no pagination) to allow client-side pagination and filtering
        $ports = $query->orderBy('name')->limit(100)->get();

        return response()->json([
            'data' => PortResource::collection($ports),
            'total' => $ports->count(),
        ]);
    }

    /**
     * GET /api/ports/{id}
     */
    public function show(int $id): JsonResponse
    {
        $port = Port::with('country')->find($id);

        if (! $port) {
            return response()->json(['message' => 'Pelabuhan tidak ditemukan.'], 404);
        }

        return response()->json(['data' => new PortResource($port)]);
    }
}