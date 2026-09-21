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

        // Determine selected barangay (null = All Barangays)
        $selectedBarangayId = $request->input('barangay_id') ?: null;
        $selectedBarangay = $selectedBarangayId ? Barangay::with('city')->find($selectedBarangayId) : null;

        // Total counts filtered by selected barangay if provided, otherwise all valid records
        if ($selectedBarangayId) {
            $totalHouseholds = Household::whereHas('address', function($q) use ($selectedBarangayId) {
                $q->where('barangay_id', $selectedBarangayId);
            })->count();

            $totalMembers = Member::whereHas('household.address', function($q) use ($selectedBarangayId) {
                $q->where('barangay_id', $selectedBarangayId);
            })->count();
        } else {
            $totalHouseholds = Household::count();
            $totalMembers = Member::whereHas('household')->count();
        }

        $memberTable = (new \App\Models\Member)->getTable();
        $hasAgeCol   = \Illuminate\Support\Facades\Schema::hasColumn($memberTable, 'age');
        $ageFallback = $hasAgeCol ? 'age' : '0';

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $ageRaw = $isSqlite
            ? "COALESCE(cast(strftime('%Y', 'now') - strftime('%Y', birth_date) as integer), {$ageFallback})"
            : "COALESCE(TIMESTAMPDIFF(YEAR, birth_date, CURDATE()), {$ageFallback})";

        $memberScope = function($query) use ($selectedBarangayId) {
            if ($selectedBarangayId) {
                $query->whereHas('household.address', fn($q) => $q->where('barangay_id', $selectedBarangayId));
            } else {
                $query->whereHas('household');
            }
        };

        $childrenCount = Member::where($memberScope)->whereRaw("({$ageRaw}) < 18")->count();
        $seniorsCount  = Member::where($memberScope)->whereRaw("({$ageRaw}) >= 60")->count();
        $pwdCount      = Member::where($memberScope)->where('is_pwd', true)->count();
        $pregnantCount = Member::where($memberScope)->where('is_pregnant', true)->count();

        // Adults = everyone who is not a child and not a senior
        $adultsCount = $totalMembers - $childrenCount - $seniorsCount;
        if ($adultsCount < 0) $adultsCount = 0;

        // Gender counts
        $maleCount   = Member::where($memberScope)->whereRaw("LOWER(sex) IN ('m', 'male')")->count();
        $femaleCount = Member::where($memberScope)->whereRaw("LOWER(sex) IN ('f', 'female')")->count();

        // Gender distribution for the table (keeps backward-compat with blade)
        $genderDistribution = collect([
            ['type' => 'Male',   'count' => $maleCount],
            ['type' => 'Female', 'count' => $femaleCount],
        ])->filter(fn($row) => $row['count'] > 0)->values();

        // Age distribution
        $ageDistribution = collect([
            ['range' => '0-5',   'count' => Member::where($memberScope)->whereRaw("({$ageRaw}) BETWEEN 0 AND 5")->count()],
            ['range' => '6-12',  'count' => Member::where($memberScope)->whereRaw("({$ageRaw}) BETWEEN 6 AND 12")->count()],
            ['range' => '13-17', 'count' => Member::where($memberScope)->whereRaw("({$ageRaw}) BETWEEN 13 AND 17")->count()],
            ['range' => '18-35', 'count' => Member::where($memberScope)->whereRaw("({$ageRaw}) BETWEEN 18 AND 35")->count()],
            ['range' => '36-59', 'count' => Member::where($memberScope)->whereRaw("({$ageRaw}) BETWEEN 36 AND 59")->count()],
            ['range' => '60+',   'count' => Member::where($memberScope)->whereRaw("({$ageRaw}) >= 60")->count()],
        ]);

        // Civil status
        $civilStatusQuery = DB::table($memberTable)
            ->join('civil_statuses', "{$memberTable}.civil_status_id", '=', 'civil_statuses.status_id')
            ->join('households', "{$memberTable}.household_id", '=', 'households.household_id')
            ->leftJoin('addresses', 'households.address_id', '=', 'addresses.address_id')
            ->select('civil_statuses.status_label as civil_status', DB::raw('COUNT(*) as count'))
            ->whereNull("{$memberTable}.deleted_at")
            ->whereNull('households.deleted_at');

        if ($selectedBarangayId) {
            $civilStatusQuery->where('addresses.barangay_id', $selectedBarangayId);
        }

        $civilStatus = $civilStatusQuery->groupBy('civil_statuses.status_label')->get();

        // Education level
        $educationLevelQuery = DB::table($memberTable)
            ->join('education_levels', "{$memberTable}.education_level_id", '=', 'education_levels.education_level_id')
            ->join('households', "{$memberTable}.household_id", '=', 'households.household_id')
            ->leftJoin('addresses', 'households.address_id', '=', 'addresses.address_id')
            ->select('education_levels.education_level_label as education_level', DB::raw('COUNT(*) as count'))
            ->whereNull("{$memberTable}.deleted_at")
            ->whereNull('households.deleted_at');

        if ($selectedBarangayId) {
            $educationLevelQuery->where('addresses.barangay_id', $selectedBarangayId);
        }

        $educationLevel = $educationLevelQuery->groupBy('education_levels.education_level_label')->get();

        // Sitio distribution — leftJoin so members without address are still counted
        $tableAgeFallback = $hasAgeCol ? "{$memberTable}.age" : "0";
        $ageExpr = $isSqlite 
            ? "COALESCE(cast(strftime('%Y', 'now') - strftime('%Y', {$memberTable}.birth_date) as integer), {$tableAgeFallback})"
            : "COALESCE(TIMESTAMPDIFF(YEAR, {$memberTable}.birth_date, CURDATE()), {$tableAgeFallback})";

        $sitioQuery = DB::table($memberTable)
            ->join('households', "{$memberTable}.household_id", '=', 'households.household_id')
            ->leftJoin('addresses', 'households.address_id', '=', 'addresses.address_id')
            ->select(
                DB::raw("COALESCE(addresses.purok_sitio, 'No Sitio') as sitio_name"),
                DB::raw('COUNT(DISTINCT households.household_id) as household_count'),
                DB::raw("COUNT({$memberTable}.member_id) as population"),
                DB::raw("SUM(CASE WHEN ({$ageExpr} < 18) THEN 1 ELSE 0 END) as children_count"),
                DB::raw("SUM(CASE WHEN ({$ageExpr} >= 60) THEN 1 ELSE 0 END) as seniors_count"),
                DB::raw("SUM(CASE WHEN ({$memberTable}.is_pwd = 1 OR EXISTS(
                    SELECT 1 FROM member_vulnerable_groups mvg 
                    JOIN vulnerable_groups vg ON mvg.vulnerable_group_id = vg.vulnerable_group_id 
                    WHERE mvg.member_id = {$memberTable}.member_id AND vg.vulnerable_group_key = 'pwd'
                )) THEN 1 ELSE 0 END) as pwd_count"),
                DB::raw("SUM(CASE WHEN ({$memberTable}.is_pregnant = 1 OR EXISTS(
                    SELECT 1 FROM member_vulnerable_groups mvg 
                    JOIN vulnerable_groups vg ON mvg.vulnerable_group_id = vg.vulnerable_group_id 
                    WHERE mvg.member_id = {$memberTable}.member_id AND vg.vulnerable_group_key = 'pregnant'
                )) THEN 1 ELSE 0 END) as pregnant_count"),
                DB::raw("SUM(CASE WHEN (
                    {$memberTable}.is_pwd = 1 OR {$memberTable}.is_pregnant = 1 OR
                    EXISTS(SELECT 1 FROM member_vulnerable_groups WHERE member_vulnerable_groups.member_id = {$memberTable}.member_id) OR 
                    ({$ageExpr} >= 60) OR 
                    ({$ageExpr} < 18)
                ) THEN 1 ELSE 0 END) as vulnerable_count")
            )
            ->whereNull("{$memberTable}.deleted_at")
            ->whereNull('households.deleted_at');

        if ($selectedBarangayId) {
            $sitioQuery->where('addresses.barangay_id', $selectedBarangayId);
        }

        $sitioDistribution = $sitioQuery
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
