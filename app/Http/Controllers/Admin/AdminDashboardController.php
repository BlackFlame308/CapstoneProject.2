<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Household;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * AdminDashboardController
 * 
 * Displays the main dashboard with statistics and summaries
 * 
 * FEATURES:
 * - Total households count
 * - Total population
 * - Demographics (children, seniors, PWD)
 * - Sitio rankings (vulnerable areas)
 * - Latest reports (placeholder for subsystems)
 * - Recent households
 */
class AdminDashboardController extends Controller
{
    public function index()
    {
        // Get statistics
        $totalHouseholds = Household::count();
        $totalPopulation = Member::count();
        
       // Get demographics
$childrenCount = Member::whereNotNull('birth_date')
    ->whereRaw('YEAR(CURDATE()) - YEAR(birth_date) < 18')
    ->count();

$seniorsCount = Member::whereNotNull('birth_date')
    ->whereRaw('YEAR(CURDATE()) - YEAR(birth_date) >= 60')
    ->count();

$pwdCount = Member::where('is_pwd', true)->count();
        
        // Get sitio rankings (most vulnerable areas)
        $sitioRankings = collect([]);
        if (class_exists('App\Models\Address')) {
            $sitioRankings = DB::table('members')
                ->join('households', 'members.household_id', '=', 'households.id')
                ->join('addresses', 'households.address_id', '=', 'addresses.id')
                ->select('addresses.purok_sitio', DB::raw('COUNT(members.id) as member_count'))
                ->groupBy('addresses.purok_sitio')
                ->orderByDesc('member_count')
                ->limit(10)
                ->get();
        }
        
        // Get recent households
        $recentHouseholds = Household::with('address')
            ->latest()
            ->limit(5)
            ->get();
        
        // Dummy data for reports (placeholder for subsystems)
        $reportsData = [
            'evacuation' => 0,
            'rescue' => 0,
            'logistics' => 0,
        ];
        
        return view('admin.dashboard', [
            'totalHouseholds' => $totalHouseholds,
            'totalPopulation' => $totalPopulation,
            'childrenCount' => $childrenCount,
            'seniorsCount' => $seniorsCount,
            'pwdCount' => $pwdCount,
            'sitioRankings' => $sitioRankings,
            'recentHouseholds' => $recentHouseholds,
            'reportsData' => $reportsData,
        ]);
    }
}
