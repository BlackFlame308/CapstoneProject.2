<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RegionController extends Controller
{
    /**
     * Get all regions
     */
    public function index(): JsonResponse
    {
        try {
            $regions = Region::with('provinces')->get();
            
            if ($regions->isEmpty()) {
                return response()->json([
                    'status' => 'info',
                    'message' => 'No regions found',
                    'data' => []
                ], 200);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Regions retrieved successfully',
                'data' => $regions
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching regions: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch regions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get provinces for a region
     */
    public function provinces(Region $region): JsonResponse
    {
        try {
            if (!$region) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Region not found'
                ], 404);
            }
            
            $provinces = $region->provinces()->get();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Provinces retrieved successfully',
                'data' => $provinces
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching provinces for region ' . $region->id . ': ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch provinces',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
