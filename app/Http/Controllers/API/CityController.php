<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    /**
     * Get barangays for a city
     */
    public function barangays(City $city): JsonResponse
    {
        try {
            if (!$city) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'City not found'
                ], 404);
            }
            
            $barangays = $city->barangays()->get();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Barangays retrieved successfully',
                'data' => $barangays
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching barangays for city ' . $city->id . ': ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch barangays',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
