<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

/**
 * AnalyticsAdminController
 * 
 * Displays analytics and statistics about the barangay
 * 
 * FEATURES:
 * - Population breakdown
 * - Demographics (children, seniors, PWD)
 * - Sitio-based population distribution
 * - Vulnerability ranking
 * - Education level distribution
 * - Civil status breakdown
 * 
 * DATA SOURCES:
 * - Member table (all demographics)
 * - Household table (location info)
 * - Address table (sitio info)
 * 
 * NOTE FOR STUDENT:
 * - Charts use Chart.js (add via CDN in views)
 * - If charts not available, displays as tables
 * - Real data comes from database queries
 * - Currently uses dummy data for subsystems
 */
class AnalyticsAdminController extends Controller
{
    public function index()
    {
        // Population stats
        $totalMembers = Member::count();
        $totalHouseholds = \App\Models\Household::count();
        
        // Demographics
        $childrenCount = Member::where(function($q) {
            $q->whereRaw('YEAR(CURDATE()) - YEAR(birth_date) < 18')
              ->whereNotNull('birth_date')
              ->orWhere('age', '<', 18);
        })->count();
        
        $seniorsCount = Member::where(function($q) {
            $q->whereRaw('YEAR(CURDATE()) - YEAR(birth_date) >= 60')
              ->whereNotNull('birth_date')
              ->orWhere('age', '>=', 60);
        })->count();
        
        $pwdCount = Member::where('is_pwd', true)->count();
        
        $pregnantCount = Member::where('is_pregnant', true)->count();
        
        // Gender distribution
        $genderDistribution = Member::select(
            DB::raw('COALESCE(gender, sex) as type'),
            DB::raw('COUNT(*) as count')
        )
        ->groupBy('type')
        ->get()
        ->map(function($item) {
            return ['type' => $item->type ?? 'Unknown', 'count' => $item->count];
        });
        
        // Age distribution
        $ageDistribution = collect([
            ['range' => '0-5', 'count' => Member::whereRaw('age BETWEEN 0 AND 5')->count()],
            ['range' => '6-12', 'count' => Member::whereRaw('age BETWEEN 6 AND 12')->count()],
            ['range' => '13-17', 'count' => Member::whereRaw('age BETWEEN 13 AND 17')->count()],
            ['range' => '18-35', 'count' => Member::whereRaw('age BETWEEN 18 AND 35')->count()],
            ['range' => '36-59', 'count' => Member::whereRaw('age BETWEEN 36 AND 59')->count()],
            ['range' => '60+', 'count' => Member::where('age', '>=', 60)->count()],
        ]);
        
        // Civil status
        $civilStatus = Member::select('civil_status', DB::raw('COUNT(*) as count'))
            ->groupBy('civil_status')
            ->whereNotNull('civil_status')
            ->get();
        
        // Education level
        $educationLevel = Member::select('education_level', DB::raw('COUNT(*) as count'))
            ->groupBy('education_level')
            ->whereNotNull('education_level')
            ->get();
        
        // Sitio distribution
        $sitioDistribution = DB::table('members')
            ->join('households', 'members.household_id', '=', 'households.id')
            ->join('addresses', 'households.address_id', '=', 'addresses.id')
            ->select('addresses.purok_sitio', DB::raw('COUNT(members.id) as population'))
            ->groupBy('addresses.purok_sitio')
            ->orderByDesc('population')
            ->limit(15)
            ->get();
        
        return view('admin.analytics.index', [
            'totalMembers' => $totalMembers,
            'totalHouseholds' => $totalHouseholds,
            'childrenCount' => $childrenCount,
            'seniorsCount' => $seniorsCount,
            'pwdCount' => $pwdCount,
            'pregnantCount' => $pregnantCount,
            'genderDistribution' => $genderDistribution,
            'ageDistribution' => $ageDistribution,
            'civilStatus' => $civilStatus,
            'educationLevel' => $educationLevel,
            'sitioDistribution' => $sitioDistribution,
        ]);
    }
}
