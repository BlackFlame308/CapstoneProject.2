<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;
use App\Models\Sitio;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function regions(): JsonResponse
    {
        return response()->json([
            'data' => Region::select('region_id as id', 'name')->orderBy('name')->get()
        ]);
    }

    public function provinces(string $regionId): JsonResponse
    {
        return response()->json([
            'data' => Province::where('region_id', $regionId)
                ->select('province_id as id', 'name')
                ->orderBy('name')
                ->get()
        ]);
    }

    public function cities(string $provinceId): JsonResponse
    {
        return response()->json([
            'data' => City::where('province_id', $provinceId)
                ->select('city_id as id', 'name')
                ->orderBy('name')
                ->get()
        ]);
    }

    public function barangays(string $cityId): JsonResponse
    {
        return response()->json([
            'data' => Barangay::where('city_id', $cityId)
                ->select('barangay_id as id', 'name')
                ->orderBy('name')
                ->get()
        ]);
    }

    public function sitios(string $barangayId): JsonResponse
    {
        return response()->json([
            'data' => Sitio::where('barangay_id', $barangayId)
                ->select('sitio_id as id', 'name')
                ->orderBy('name')
                ->get()
        ]);
    }
}
