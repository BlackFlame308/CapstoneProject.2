<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProvinceController extends Controller
{
    /**
     * Get cities for a province
     */
    public function cities(Province $province): JsonResponse
    {
        try {
            if (!$province) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Province not found'
                ], 404);
            }
            
            $cities = $province->cities()->get();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Cities retrieved successfully',
                'data' => $cities
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching cities for province ' . $province->id . ': ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch cities',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
