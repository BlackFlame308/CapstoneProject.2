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
        $mambaling = \App\Models\Barangay::where('name', 'like', 'Mambaling')->first();
        $mambalingId = $mambaling?->barangay_id ?? 396;

        // Get statistics (scoping to Mambaling)
        $totalHouseholds = Household::whereHas('address', function($q) use ($mambalingId) {
            $q->where('barangay_id', $mambalingId);
        })->count();

        $totalPopulation = Member::whereHas('household.address', function($q) use ($mambalingId) {
            $q->where('barangay_id', $mambalingId);
        })->count();

        $adultCutoff = now()->subYears(18)->toDateString();
        $seniorCutoff = now()->subYears(60)->toDateString();
        
        // Get demographics (scoping to Mambaling)
        $childrenCount = Member::whereHas('household.address', function($q) use ($mambalingId) {
            $q->where('barangay_id', $mambalingId);
        })->where(function ($query) use ($adultCutoff) {
            $query->whereDate('birth_date', '>', $adultCutoff)
                ->orWhere(function ($fallback) {
                    $fallback->whereNull('birth_date')->where('age', '<', 18);
                });
        })->count();

        $seniorsCount = Member::whereHas('household.address', function($q) use ($mambalingId) {
            $q->where('barangay_id', $mambalingId);
        })->where(function ($query) use ($seniorCutoff) {
            $query->whereDate('birth_date', '<=', $seniorCutoff)
                ->orWhere(function ($fallback) {
                    $fallback->whereNull('birth_date')->where('age', '>=', 60);
                });
        })->count();

        $pwdCount = Member::whereHas('household.address', function($q) use ($mambalingId) {
            $q->where('barangay_id', $mambalingId);
        })->where('is_pwd', true)->count();

        $pregnantCount = Member::whereHas('household.address', function($q) use ($mambalingId) {
            $q->where('barangay_id', $mambalingId);
        })->where('is_pregnant', true)->count();

        $adultsCount = Member::whereHas('household.address', function($q) use ($mambalingId) {
            $q->where('barangay_id', $mambalingId);
        })->where(function ($query) use ($adultCutoff, $seniorCutoff) {
            $query->whereBetween('birth_date', [$seniorCutoff, $adultCutoff])
                ->orWhere(function ($fallback) {
                    $fallback->whereNull('birth_date')->whereBetween('age', [18, 59]);
                });
        })->count();
        
        // Get sitio rankings (most vulnerable areas ranked by unique vulnerable resident count)
        $sitioRankings = collect([]);
        if (class_exists('App\Models\Address')) {
            $isSqlite = DB::connection()->getDriverName() === 'sqlite';
            $memberTable = (new \App\Models\Member)->getTable();
            $ageExpr = $isSqlite 
                ? "COALESCE(cast(strftime('%Y', 'now') - strftime('%Y', {$memberTable}.birth_date) as integer), {$memberTable}.age)"
                : "COALESCE(TIMESTAMPDIFF(YEAR, {$memberTable}.birth_date, CURDATE()), {$memberTable}.age)";

            $sitioRankings = DB::table($memberTable)
                ->join('households', "{$memberTable}.household_id", '=', 'households.household_id')
                ->join('addresses', 'households.address_id', '=', 'addresses.address_id')
                ->select(
                    'addresses.purok_sitio',
                    DB::raw("COUNT({$memberTable}.member_id) as member_count"),
                    DB::raw("SUM(CASE WHEN (
                        {$memberTable}.is_pwd = 1 OR 
                        {$memberTable}.is_pregnant = 1 OR 
                        ({$ageExpr} >= 60) OR 
                        ({$ageExpr} < 18)
                    ) THEN 1 ELSE 0 END) as vulnerable_count")
                )
                ->whereNull("{$memberTable}.deleted_at")
                ->whereNull('households.deleted_at')
                ->where('addresses.barangay_id', $mambalingId) // Filter by Mambaling barangay_id!
                ->groupBy('addresses.purok_sitio')
                ->get()
                ->sortByDesc('vulnerable_count')
                ->take(10)
                ->values();
        }
        
        // Get recent households (scoping to Mambaling)
        $recentHouseholds = Household::with('address')
            ->whereHas('address', function($q) use ($mambalingId) {
                $q->where('barangay_id', $mambalingId);
            })
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
