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
        // Total counts
        $totalHouseholds = \App\Models\Household::count();
        $totalMembers    = Member::count();

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $ageRaw = $isSqlite
            ? "COALESCE(cast(strftime('%Y', 'now') - strftime('%Y', birth_date) as integer), age)"
            : "COALESCE(TIMESTAMPDIFF(YEAR, birth_date, CURDATE()), age)";

        $childrenCount = Member::whereRaw("({$ageRaw}) < 18")->count();
        $seniorsCount = Member::whereRaw("({$ageRaw}) >= 60")->count();

        $pwdCount      = Member::where('is_pwd', true)->count();
        $pregnantCount = Member::where('is_pregnant', true)->count();

        // Adults = everyone who is not a child and not a senior
        $adultsCount = $totalMembers - $childrenCount - $seniorsCount;
        if ($adultsCount < 0) $adultsCount = 0;

        // Gender counts — handle both stored formats: 'M'/'F' and 'Male'/'Female'
        $maleCount = Member::whereRaw("LOWER(sex) IN ('m', 'male')")->count();
        $femaleCount = Member::whereRaw("LOWER(sex) IN ('f', 'female')")->count();

        // Gender distribution for the table (keeps backward-compat with blade)
        $genderDistribution = collect([
            ['type' => 'Male',   'count' => $maleCount],
            ['type' => 'Female', 'count' => $femaleCount],
        ])->filter(fn($row) => $row['count'] > 0)->values();

        // Age distribution
        $ageDistribution = collect([
            ['range' => '0-5',   'count' => Member::whereRaw("({$ageRaw}) BETWEEN 0 AND 5")->count()],
            ['range' => '6-12',  'count' => Member::whereRaw("({$ageRaw}) BETWEEN 6 AND 12")->count()],
            ['range' => '13-17', 'count' => Member::whereRaw("({$ageRaw}) BETWEEN 13 AND 17")->count()],
            ['range' => '18-35', 'count' => Member::whereRaw("({$ageRaw}) BETWEEN 18 AND 35")->count()],
            ['range' => '36-59', 'count' => Member::whereRaw("({$ageRaw}) BETWEEN 36 AND 59")->count()],
            ['range' => '60+',   'count' => Member::whereRaw("({$ageRaw}) >= 60")->count()],
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

        // Sitio distribution — leftJoin so members without address are still counted
        $ageExpr = $isSqlite 
            ? "COALESCE(cast(strftime('%Y', 'now') - strftime('%Y', members.birth_date) as integer), members.age)"
            : "COALESCE(TIMESTAMPDIFF(YEAR, members.birth_date, CURDATE()), members.age)";

        $sitioDistribution = DB::table('members')
            ->join('households', 'members.household_id', '=', 'households.household_id')
            ->leftJoin('addresses', 'households.address_id', '=', 'addresses.address_id')
            ->select(
                DB::raw("COALESCE(addresses.purok_sitio, 'No Sitio') as sitio_name"),
                DB::raw('COUNT(DISTINCT households.household_id) as household_count'),
                DB::raw('COUNT(members.member_id) as population'),
                DB::raw("SUM(CASE WHEN ({$ageExpr} < 18) THEN 1 ELSE 0 END) as children_count"),
                DB::raw("SUM(CASE WHEN ({$ageExpr} >= 60) THEN 1 ELSE 0 END) as seniors_count"),
                DB::raw('SUM(CASE WHEN members.is_pwd = 1 THEN 1 ELSE 0 END) as pwd_count'),
                DB::raw('SUM(CASE WHEN members.is_pregnant = 1 THEN 1 ELSE 0 END) as pregnant_count'),
                DB::raw("SUM(CASE WHEN (
                    members.is_pwd = 1 OR 
                    members.is_pregnant = 1 OR 
                    ({$ageExpr} >= 60) OR 
                    ({$ageExpr} < 18)
                ) THEN 1 ELSE 0 END) as vulnerable_count")
            )
            ->whereNull('members.deleted_at')
            ->whereNull('households.deleted_at')
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
