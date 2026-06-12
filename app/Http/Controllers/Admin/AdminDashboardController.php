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
                        EXISTS(SELECT 1 FROM member_vulnerable_groups WHERE member_vulnerable_groups.member_id = {$memberTable}.member_id) OR 
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
        
        // Fetch actual report counts
        $evacuationCount = \Illuminate\Support\Facades\Schema::hasTable('evacuation_records')
            ? \Illuminate\Support\Facades\DB::table('evacuation_records')->count()
            : 0;
            
        $rescueCount = \Illuminate\Support\Facades\Schema::hasTable('responder_assignments')
            ? \Illuminate\Support\Facades\DB::table('responder_assignments')->count()
            : 0;
            
        $logisticsCount = \Illuminate\Support\Facades\Schema::hasTable('resource_requests')
            ? \Illuminate\Support\Facades\DB::table('resource_requests')->count()
            : 0;

        $reportsData = [
            'evacuation' => $evacuationCount,
            'rescue' => $rescueCount,
            'logistics' => $logisticsCount,
        ];

        // Fetch latest reports
        $latestReports = collect([]);

        if ($evacuationCount > 0) {
            $evacuations = DB::table('evacuation_records')
                ->leftJoin('disaster_events', 'evacuation_records.event_id', '=', 'disaster_events.event_id')
                ->leftJoin('evacuation_centers', 'evacuation_records.center_id', '=', 'evacuation_centers.evacuation_center_id')
                ->select('evacuation_records.created_at', 'disaster_events.name as event_name', 'evacuation_centers.name as center_name', 'evacuation_records.evacuated_count')
                ->orderByDesc('evacuation_records.created_at')
                ->limit(5)
                ->get()
                ->map(function ($r) {
                    return [
                        'type' => 'Evacuation',
                        'title' => $r->event_name ?? 'Disaster Incident',
                        'detail' => ($r->center_name ? 'Evacuating to ' . $r->center_name : 'Evacuation incident') . ' (' . ($r->evacuated_count ?? 0) . ' residents)',
                        'date' => $r->created_at,
                        'badge' => 'bg-primary',
                    ];
                });
            $latestReports = $latestReports->concat($evacuations);
        }

        if ($rescueCount > 0) {
            $rescues = DB::table('responder_assignments')
                ->leftJoin('rescue_teams', 'responder_assignments.team_id', '=', 'rescue_teams.team_id')
                ->select('responder_assignments.assigned_at', 'rescue_teams.team_name', 'responder_assignments.status')
                ->orderByDesc('responder_assignments.assigned_at')
                ->limit(5)
                ->get()
                ->map(function ($r) {
                    return [
                        'type' => 'Rescue',
                        'title' => $r->team_name ?? 'Rescue Operation',
                        'detail' => 'Team assigned. Status: ' . ($r->status ?? 'Assigned'),
                        'date' => $r->assigned_at,
                        'badge' => 'bg-success',
                    ];
                });
            $latestReports = $latestReports->concat($rescues);
        }

        if ($logisticsCount > 0) {
            $logistics = DB::table('resource_requests')
                ->leftJoin('evacuation_centers', 'resource_requests.evacuation_center_id', '=', 'evacuation_centers.evacuation_center_id')
                ->select('resource_requests.created_at', 'resource_requests.resource_type', 'resource_requests.quantity', 'evacuation_centers.name as center_name')
                ->orderByDesc('resource_requests.created_at')
                ->limit(5)
                ->get()
                ->map(function ($r) {
                    return [
                        'type' => 'Logistics',
                        'title' => $r->resource_type ?? 'Supply Request',
                        'detail' => 'Requested ' . ($r->quantity ?? 1) . ' units for ' . ($r->center_name ?? 'Center'),
                        'date' => $r->created_at,
                        'badge' => 'bg-warning text-dark',
                    ];
                });
            $latestReports = $latestReports->concat($logistics);
        }

        $latestReports = $latestReports->sortByDesc('date')->take(5)->values();
        
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
            'latestReports' => $latestReports,
        ]);
    }
}
