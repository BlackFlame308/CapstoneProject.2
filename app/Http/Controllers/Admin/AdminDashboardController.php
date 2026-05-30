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
        $adultCutoff = now()->subYears(18)->toDateString();
        $seniorCutoff = now()->subYears(60)->toDateString();
        
        // Get demographics
        $childrenCount = Member::where(function ($query) use ($adultCutoff) {
            $query->whereDate('birth_date', '>', $adultCutoff)
                ->orWhere(function ($fallback) {
                    $fallback->whereNull('birth_date')->where('age', '<', 18);
                });
        })->count();

        $seniorsCount = Member::where(function ($query) use ($seniorCutoff) {
            $query->whereDate('birth_date', '<=', $seniorCutoff)
                ->orWhere(function ($fallback) {
                    $fallback->whereNull('birth_date')->where('age', '>=', 60);
                });
        })->count();

        $pwdCount = Member::where('is_pwd', true)->count();
        $pregnantCount = Member::where('is_pregnant', true)->count();
        $adultsCount = Member::where(function ($query) use ($adultCutoff, $seniorCutoff) {
            $query->whereBetween('birth_date', [$seniorCutoff, $adultCutoff])
                ->orWhere(function ($fallback) {
                    $fallback->whereNull('birth_date')->whereBetween('age', [18, 59]);
                });
        })->count();
        
        // Get sitio rankings (most vulnerable areas ranked by unique vulnerable resident count)
        $sitioRankings = collect([]);
        if (class_exists('App\Models\Address')) {
            $isSqlite = DB::connection()->getDriverName() === 'sqlite';
            $ageExpr = $isSqlite 
                ? "COALESCE(cast(strftime('%Y', 'now') - strftime('%Y', members.birth_date) as integer), members.age)"
                : "COALESCE(TIMESTAMPDIFF(YEAR, members.birth_date, CURDATE()), members.age)";

            $sitioRankings = DB::table('members')
                ->join('households', 'members.household_id', '=', 'households.household_id')
                ->join('addresses', 'households.address_id', '=', 'addresses.address_id')
                ->select(
                    'addresses.purok_sitio',
                    DB::raw('COUNT(members.member_id) as member_count'),
                    DB::raw("SUM(CASE WHEN (
                        members.is_pwd = 1 OR 
                        members.is_pregnant = 1 OR 
                        ({$ageExpr} >= 60) OR 
                        ({$ageExpr} < 18)
                    ) THEN 1 ELSE 0 END) as vulnerable_count")
                )
                ->whereNull('members.deleted_at')
                ->whereNull('households.deleted_at')
                ->groupBy('addresses.purok_sitio')
                ->get()
                ->sortByDesc('vulnerable_count')
                ->take(10)
                ->values();
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
            'pregnantCount' => $pregnantCount,
            'adultsCount' => $adultsCount,
            'sitioRankings' => $sitioRankings,
            'recentHouseholds' => $recentHouseholds,
            'reportsData' => $reportsData,
        ]);
    }
}
