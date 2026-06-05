<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Household;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * AnalyticsAdminController
 * 
 * Displays analytics and statistics about the barangay
 */
class AnalyticsAdminController extends Controller
{
    public function index(Request $request)
    {
        // Get all barangays for the filter selector
        $availableBarangays = Barangay::with('city')->get()->sortBy('name')->values();

        // Determine selected barangay
        $selectedBarangayId = $request->input('barangay_id');
        if (empty($selectedBarangayId)) {
            // Focus on Mambaling barangay as the default
            $mambaling = Barangay::where('name', 'like', 'Mambaling')->first();
            if ($mambaling) {
                $selectedBarangayId = $mambaling->barangay_id;
            } else {
                // Default to the barangay with the most households in active database
                $mostPopulated = DB::table('addresses')
                    ->join('households', 'addresses.address_id', '=', 'households.address_id')
                    ->whereNull('households.deleted_at')
                    ->groupBy('addresses.barangay_id')
                    ->select('addresses.barangay_id', DB::raw('COUNT(*) as count'))
                    ->orderByDesc('count')
                    ->first();
                    
                $selectedBarangayId = $mostPopulated ? $mostPopulated->barangay_id : (Barangay::first()?->barangay_id ?? null);
            }
        }

        $selectedBarangay = $selectedBarangayId ? Barangay::with('city')->find($selectedBarangayId) : null;

        // Total counts filtered by selected barangay
        $totalHouseholds = Household::whereHas('address', function($q) use ($selectedBarangayId) {
            $q->where('barangay_id', $selectedBarangayId);
        })->count();

        $totalMembers = Member::whereHas('household.address', function($q) use ($selectedBarangayId) {
            $q->where('barangay_id', $selectedBarangayId);
        })->count();

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $ageRaw = $isSqlite
            ? "COALESCE(cast(strftime('%Y', 'now') - strftime('%Y', birth_date) as integer), age)"
            : "COALESCE(TIMESTAMPDIFF(YEAR, birth_date, CURDATE()), age)";

        $childrenCount = Member::whereHas('household.address', function($q) use ($selectedBarangayId) {
            $q->where('barangay_id', $selectedBarangayId);
        })->whereRaw("({$ageRaw}) < 18")->count();

        $seniorsCount = Member::whereHas('household.address', function($q) use ($selectedBarangayId) {
            $q->where('barangay_id', $selectedBarangayId);
        })->whereRaw("({$ageRaw}) >= 60")->count();

        $pwdCount = Member::whereHas('household.address', function($q) use ($selectedBarangayId) {
            $q->where('barangay_id', $selectedBarangayId);
        })->where('is_pwd', true)->count();

        $pregnantCount = Member::whereHas('household.address', function($q) use ($selectedBarangayId) {
            $q->where('barangay_id', $selectedBarangayId);
        })->where('is_pregnant', true)->count();

        // Adults = everyone who is not a child and not a senior
        $adultsCount = $totalMembers - $childrenCount - $seniorsCount;
        if ($adultsCount < 0) $adultsCount = 0;

        // Gender counts
        $maleCount = Member::whereHas('household.address', function($q) use ($selectedBarangayId) {
            $q->where('barangay_id', $selectedBarangayId);
        })->whereRaw("LOWER(sex) IN ('m', 'male')")->count();

        $femaleCount = Member::whereHas('household.address', function($q) use ($selectedBarangayId) {
            $q->where('barangay_id', $selectedBarangayId);
        })->whereRaw("LOWER(sex) IN ('f', 'female')")->count();

        // Gender distribution for the table (keeps backward-compat with blade)
        $genderDistribution = collect([
            ['type' => 'Male',   'count' => $maleCount],
            ['type' => 'Female', 'count' => $femaleCount],
        ])->filter(fn($row) => $row['count'] > 0)->values();

        // Age distribution
        $ageDistribution = collect([
            ['range' => '0-5',   'count' => Member::whereHas('household.address', function($q) use ($selectedBarangayId) {
                $q->where('barangay_id', $selectedBarangayId);
            })->whereRaw("({$ageRaw}) BETWEEN 0 AND 5")->count()],
            ['range' => '6-12',  'count' => Member::whereHas('household.address', function($q) use ($selectedBarangayId) {
                $q->where('barangay_id', $selectedBarangayId);
            })->whereRaw("({$ageRaw}) BETWEEN 6 AND 12")->count()],
            ['range' => '13-17', 'count' => Member::whereHas('household.address', function($q) use ($selectedBarangayId) {
                $q->where('barangay_id', $selectedBarangayId);
            })->whereRaw("({$ageRaw}) BETWEEN 13 AND 17")->count()],
            ['range' => '18-35', 'count' => Member::whereHas('household.address', function($q) use ($selectedBarangayId) {
                $q->where('barangay_id', $selectedBarangayId);
            })->whereRaw("({$ageRaw}) BETWEEN 18 AND 35")->count()],
            ['range' => '36-59', 'count' => Member::whereHas('household.address', function($q) use ($selectedBarangayId) {
                $q->where('barangay_id', $selectedBarangayId);
            })->whereRaw("({$ageRaw}) BETWEEN 36 AND 59")->count()],
            ['range' => '60+',   'count' => Member::whereHas('household.address', function($q) use ($selectedBarangayId) {
                $q->where('barangay_id', $selectedBarangayId);
            })->whereRaw("({$ageRaw}) >= 60")->count()],
        ]);

        // Civil status
        $memberTable = (new \App\Models\Member)->getTable();

        if (config('database.default') === 'sqlite') {
            $civilStatus = DB::table($memberTable)
                ->join('households', "{$memberTable}.household_id", '=', 'households.household_id')
                ->join('addresses', 'households.address_id', '=', 'addresses.address_id')
                ->select("{$memberTable}.civil_status as civil_status", DB::raw('COUNT(*) as count'))
                ->whereNull("{$memberTable}.deleted_at")
                ->whereNull('households.deleted_at')
                ->where('addresses.barangay_id', $selectedBarangayId)
                ->groupBy("{$memberTable}.civil_status")
                ->get();
        } else {
            $civilStatus = DB::table($memberTable)
                ->join('civil_statuses', "{$memberTable}.civil_status_id", '=', 'civil_statuses.status_id')
                ->join('households', "{$memberTable}.household_id", '=', 'households.household_id')
                ->join('addresses', 'households.address_id', '=', 'addresses.address_id')
                ->select('civil_statuses.status_label as civil_status', DB::raw('COUNT(*) as count'))
                ->whereNull("{$memberTable}.deleted_at")
                ->whereNull('households.deleted_at')
                ->where('addresses.barangay_id', $selectedBarangayId)
                ->groupBy('civil_statuses.status_label')
                ->get();
        }

        // Education level
        if (config('database.default') === 'sqlite') {
            $educationLevel = DB::table($memberTable)
                ->join('households', "{$memberTable}.household_id", '=', 'households.household_id')
                ->join('addresses', 'households.address_id', '=', 'addresses.address_id')
                ->select("{$memberTable}.education_level as education_level", DB::raw('COUNT(*) as count'))
                ->whereNull("{$memberTable}.deleted_at")
                ->whereNull('households.deleted_at')
                ->where('addresses.barangay_id', $selectedBarangayId)
                ->groupBy("{$memberTable}.education_level")
                ->get();
        } else {
            $educationLevel = DB::table($memberTable)
                ->join('education_levels', "{$memberTable}.education_level_id", '=', 'education_levels.education_level_id')
                ->join('households', "{$memberTable}.household_id", '=', 'households.household_id')
                ->join('addresses', 'households.address_id', '=', 'addresses.address_id')
                ->select('education_levels.education_level_label as education_level', DB::raw('COUNT(*) as count'))
                ->whereNull("{$memberTable}.deleted_at")
                ->whereNull('households.deleted_at')
                ->where('addresses.barangay_id', $selectedBarangayId)
                ->groupBy('education_levels.education_level_label')
                ->get();
        }

        // Sitio distribution — leftJoin so members without address are still counted
        $ageExpr = $isSqlite 
            ? "COALESCE(cast(strftime('%Y', 'now') - strftime('%Y', {$memberTable}.birth_date) as integer), {$memberTable}.age)"
            : "COALESCE(TIMESTAMPDIFF(YEAR, {$memberTable}.birth_date, CURDATE()), {$memberTable}.age)";

        $sitioDistribution = DB::table($memberTable)
            ->join('households', "{$memberTable}.household_id", '=', 'households.household_id')
            ->leftJoin('addresses', 'households.address_id', '=', 'addresses.address_id')
            ->select(
                DB::raw("COALESCE(addresses.purok_sitio, 'No Sitio') as sitio_name"),
                DB::raw('COUNT(DISTINCT households.household_id) as household_count'),
                DB::raw("COUNT({$memberTable}.member_id) as population"),
                DB::raw("SUM(CASE WHEN ({$ageExpr} < 18) THEN 1 ELSE 0 END) as children_count"),
                DB::raw("SUM(CASE WHEN ({$ageExpr} >= 60) THEN 1 ELSE 0 END) as seniors_count"),
                DB::raw("SUM(CASE WHEN {$memberTable}.is_pwd = 1 THEN 1 ELSE 0 END) as pwd_count"),
                DB::raw("SUM(CASE WHEN {$memberTable}.is_pregnant = 1 THEN 1 ELSE 0 END) as pregnant_count"),
                DB::raw("SUM(CASE WHEN (
                    {$memberTable}.is_pwd = 1 OR 
                    {$memberTable}.is_pregnant = 1 OR 
                    ({$ageExpr} >= 60) OR 
                    ({$ageExpr} < 18)
                ) THEN 1 ELSE 0 END) as vulnerable_count")
            )
            ->whereNull("{$memberTable}.deleted_at")
            ->whereNull('households.deleted_at')
            ->where('addresses.barangay_id', $selectedBarangayId)
            ->groupBy('sitio_name')
            ->get()
            ->sortByDesc('vulnerable_count')
            ->values();

        // Vulnerability score: children×1 + seniors×1.5 + pwd×2 + pregnant×1.5
        $sitioRankings = $sitioDistribution->map(function ($row) {
            $row->vulnerability_score =
                ($row->children_count * 1.0) +
                ($row->seniors_count  * 1.5) +
                ($row->pwd_count      * 2.0) +
                ($row->pregnant_count * 1.5);
            return $row;
        })->sortByDesc('vulnerability_score')->values();

        return view('admin.analytics.index', [
            'availableBarangays' => $availableBarangays,
            'selectedBarangayId' => $selectedBarangayId,
            'selectedBarangay'   => $selectedBarangay,
            'totalHouseholds'   => $totalHouseholds,
            'totalMembers'      => $totalMembers,
            'childrenCount'     => $childrenCount,
            'seniorsCount'      => $seniorsCount,
            'pwdCount'          => $pwdCount,
            'pregnantCount'     => $pregnantCount,
            'adultsCount'       => $adultsCount,
            'maleCount'         => $maleCount,
            'femaleCount'       => $femaleCount,
            'genderDistribution' => $genderDistribution,
            'ageDistribution'   => $ageDistribution,
            'civilStatus'       => $civilStatus,
            'educationLevel'    => $educationLevel,
            'sitioDistribution' => $sitioDistribution,
            'sitioRankings'     => $sitioRankings,
        ]);
    }
}
