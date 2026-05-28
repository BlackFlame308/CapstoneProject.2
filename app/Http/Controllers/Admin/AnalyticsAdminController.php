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

        // Use TIMESTAMPDIFF so calculations are always current (not stale 'age' column)
        $childrenCount = Member::whereRaw(
            'TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) < 18'
        )->whereNotNull('birth_date')->count();

        $seniorsCount = Member::whereRaw(
            'TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= 60'
        )->whereNotNull('birth_date')->count();

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

        // Age distribution — use TIMESTAMPDIFF so it never uses stale 'age' column
        $ageDistribution = collect([
            ['range' => '0-5',   'count' => Member::whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 0 AND 5')->whereNotNull('birth_date')->count()],
            ['range' => '6-12',  'count' => Member::whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 6 AND 12')->whereNotNull('birth_date')->count()],
            ['range' => '13-17', 'count' => Member::whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 13 AND 17')->whereNotNull('birth_date')->count()],
            ['range' => '18-35', 'count' => Member::whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 18 AND 35')->whereNotNull('birth_date')->count()],
            ['range' => '36-59', 'count' => Member::whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 36 AND 59')->whereNotNull('birth_date')->count()],
            ['range' => '60+',   'count' => Member::whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= 60')->whereNotNull('birth_date')->count()],
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
        $sitioDistribution = DB::table('members')
            ->join('households', 'members.household_id', '=', 'households.household_id')
            ->leftJoin('addresses', 'households.address_id', '=', 'addresses.address_id')
            ->select(
                DB::raw("COALESCE(addresses.purok_sitio, 'No Sitio') as sitio_name"),
                DB::raw('COUNT(DISTINCT households.household_id) as household_count'),
                DB::raw('COUNT(members.member_id) as population'),
                DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(YEAR, members.birth_date, CURDATE()) < 18 AND members.birth_date IS NOT NULL THEN 1 ELSE 0 END) as children_count'),
                DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(YEAR, members.birth_date, CURDATE()) >= 60 AND members.birth_date IS NOT NULL THEN 1 ELSE 0 END) as seniors_count'),
                DB::raw('SUM(CASE WHEN members.is_pwd = 1 THEN 1 ELSE 0 END) as pwd_count'),
                DB::raw('SUM(CASE WHEN members.is_pregnant = 1 THEN 1 ELSE 0 END) as pregnant_count')
            )
            ->whereNull('members.deleted_at')
            ->whereNull('households.deleted_at')
            ->groupBy('sitio_name')
            ->orderByDesc('population')
            ->get();

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
