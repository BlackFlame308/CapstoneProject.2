<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Analytic;
use App\Models\Barangay;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnalyticController extends Controller
{
    public function barangay(): JsonResponse
    {
        try {
            $analytics = Analytic::whereNotNull('barangay_id')->with('barangay')->get();

            return response()->json([
                'status' => 'success',
                'data' => $analytics,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('API Analytic barangay error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve barangay analytics',
            ], 500);
        }
    }

    public function sitio(): JsonResponse
    {
        try {
            $analytics = Analytic::whereNotNull('sitio')->with('barangay')->get();

            return response()->json([
                'status' => 'success',
                'data' => $analytics,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('API Analytic sitio error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve sitio analytics',
            ], 500);
        }
    }

    public function refresh(): JsonResponse
    {
        try {
            $barangays = Barangay::all();
            foreach ($barangays as $barangay) {
                $households = DB::table('households')
                    ->join('addresses', 'households.address_id', '=', 'addresses.id')
                    ->where('addresses.barangay_id', $barangay->id)
                    ->count();

                $population = DB::table('members')
                    ->join('households', 'members.household_id', '=', 'households.id')
                    ->join('addresses', 'households.address_id', '=', 'addresses.id')
                    ->where('addresses.barangay_id', $barangay->id)
                    ->count();

                $pwd = DB::table('members')
                    ->join('households', 'members.household_id', '=', 'households.id')
                    ->join('addresses', 'households.address_id', '=', 'addresses.id')
                    ->where('addresses.barangay_id', $barangay->id)
                    ->where('members.is_pwd', true)
                    ->count();

                $seniors = DB::table('members')
                    ->join('households', 'members.household_id', '=', 'households.id')
                    ->join('addresses', 'households.address_id', '=', 'addresses.id')
                    ->where('addresses.barangay_id', $barangay->id)
                    ->whereRaw('YEAR(CURDATE()) - YEAR(members.birth_date) >= 60')
                    ->count();

                Analytic::updateOrCreate(
                    ['barangay_id' => $barangay->id, 'sitio' => null],
                    [
                        'total_households' => $households,
                        'total_population' => $population,
                        'total_pwd' => $pwd,
                        'total_seniors' => $seniors,
                    ]
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Analytics refreshed successfully',
            ], 200);
        } catch (\Exception $e) {
            \Log::error('API Analytic refresh error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to refresh analytics',
            ], 500);
        }
    }
}
