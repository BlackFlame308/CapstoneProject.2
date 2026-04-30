<?php

namespace App\Services;

use App\Models\Analytic;
use App\Models\Household;
use App\Models\Barangay;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Aggregate key metrics for the dashboard.
     */
    public function getStats(): array
    {
        return [
            'totalHouseholds' => Household::count(),
            'totalMembers'    => Member::count(),
            'totalPWD'        => Member::where('is_pwd', true)->count(),
            'totalSeniors'    => Member::whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= 60')->count(),
            'totalUsers'      => User::count(),
            'totalCaptains'   => User::whereHas('role', fn($q) => $q->where('name', 'Captain'))->count(),
        ];
    }

    /**
     * Get age distribution breakdown.
     */
    public function getAgeDistribution(): array
    {
        return [
            'children' => Member::whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) < 18')->count(),
            'adults'   => Member::whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 18 AND 59')->count(),
            'seniors'  => Member::whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= 60')->count(),
        ];
    }

    /**
     * Get barangay-level statistics with pre-computed analytics.
     */
    public function getBarangayStats(): array
    {
        $barangays = Barangay::with(['analytics' => function($q) {
                $q->whereNull('purok_sitio')->orderBy('record_period', 'desc');
            }])
            ->withCount(['addresses as households_count' => fn($q) => $q->whereHas('household')])
            ->get();

        return $barangays->map(fn ($b) => [
            'id'                => $b->id,
            'name'              => $b->name,
            'households_count'  => $b->households_count,
            'analytics'         => $b->analytics->first(),
        ])->all();
    }

    /**
     * Get member counts grouped by barangay for charting.
     */
    public function getMembersByBarangay(): array
    {
        return DB::table('members')
            ->join('households', 'members.household_id', '=', 'households.id')
            ->join('addresses', 'households.address_id', '=', 'addresses.id')
            ->join('barangays', 'addresses.barangay_id', '=', 'barangays.id')
            ->groupBy('barangays.id', 'barangays.name')
            ->select('barangays.name', DB::raw('COUNT(members.id) as count'))
            ->orderBy('barangays.name')
            ->get()
            ->all();
    }

    /**
     * Get recently added households.
     */
    public function getRecentHouseholds(int $limit = 5): array
    {
        return Household::with([
                'address.barangay.city.province.region',
                'members',
            ])
            ->latest()
            ->take($limit)
            ->get()
            ->all();
    }

    /**
     * Refresh analytics using bulk SQL aggregation.
     * Takes a snapshot of demographics per barangay and sitio for the current month.
     */
    public function refreshAnalytics(): int
    {
        $period = now()->startOfMonth()->toDateString();
        $updatedCount = 0;

        DB::transaction(function () use ($period, &$updatedCount) {
            // 1. Calculate household counts grouped by barangay and sitio
            $householdCounts = DB::table('households')
                ->join('addresses', 'households.address_id', '=', 'addresses.id')
                ->select(
                    'addresses.barangay_id',
                    'addresses.purok_sitio',
                    DB::raw('COUNT(households.id) as total_households')
                )
                ->whereNotNull('addresses.barangay_id')
                ->groupBy('addresses.barangay_id', 'addresses.purok_sitio')
                ->get();

            // 2. Calculate member demographics grouped by barangay and sitio
            $memberStats = DB::table('members')
                ->join('households', 'members.household_id', '=', 'households.id')
                ->join('addresses', 'households.address_id', '=', 'addresses.id')
                ->select(
                    'addresses.barangay_id',
                    'addresses.purok_sitio',
                    DB::raw('COUNT(members.id) as total_population'),
                    DB::raw('SUM(CASE WHEN members.sex = "male" THEN 1 ELSE 0 END) as total_males'),
                    DB::raw('SUM(CASE WHEN members.sex = "female" THEN 1 ELSE 0 END) as total_females'),
                    DB::raw('SUM(CASE WHEN members.is_pwd = 1 THEN 1 ELSE 0 END) as total_pwd'),
                    DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(YEAR, members.birth_date, CURDATE()) >= 60 THEN 1 ELSE 0 END) as total_seniors'),
                    DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(YEAR, members.birth_date, CURDATE()) < 18 THEN 1 ELSE 0 END) as total_children'),
                    DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(YEAR, members.birth_date, CURDATE()) BETWEEN 18 AND 59 THEN 1 ELSE 0 END) as total_adults')
                )
                ->whereNotNull('addresses.barangay_id')
                ->groupBy('addresses.barangay_id', 'addresses.purok_sitio')
                ->get();

            $dataMap = [];
            
            foreach ($householdCounts as $hc) {
                $key = $hc->barangay_id . '-' . ($hc->purok_sitio ?: 'null');
                $dataMap[$key] = [
                    'barangay_id'      => $hc->barangay_id,
                    'purok_sitio'      => $hc->purok_sitio,
                    'total_households' => $hc->total_households,
                    'total_population' => 0,
                    'total_males'      => 0,
                    'total_females'    => 0,
                    'total_pwd'        => 0,
                    'total_seniors'    => 0,
                    'total_children'   => 0,
                    'total_adults'     => 0,
                ];
            }

            foreach ($memberStats as $ms) {
                $key = $ms->barangay_id . '-' . ($ms->purok_sitio ?: 'null');
                if (!isset($dataMap[$key])) {
                    $dataMap[$key] = [
                        'barangay_id'      => $ms->barangay_id,
                        'purok_sitio'      => $ms->purok_sitio,
                        'total_households' => 0,
                    ];
                }
                
                $dataMap[$key]['total_population'] = $ms->total_population;
                $dataMap[$key]['total_males']      = $ms->total_males;
                $dataMap[$key]['total_females']    = $ms->total_females;
                $dataMap[$key]['total_pwd']        = $ms->total_pwd;
                $dataMap[$key]['total_seniors']    = $ms->total_seniors;
                $dataMap[$key]['total_children']   = $ms->total_children;
                $dataMap[$key]['total_adults']     = $ms->total_adults;
            }

            foreach ($dataMap as $data) {
                Analytic::updateOrCreate(
                    [
                        'barangay_id'   => $data['barangay_id'],
                        'purok_sitio'   => $data['purok_sitio'],
                        'record_period' => $period,
                    ],
                    [
                        'total_households' => $data['total_households'] ?? 0,
                        'total_population' => $data['total_population'] ?? 0,
                        'total_males'      => $data['total_males'] ?? 0,
                        'total_females'    => $data['total_females'] ?? 0,
                        'total_pwd'        => $data['total_pwd'] ?? 0,
                        'total_seniors'    => $data['total_seniors'] ?? 0,
                        'total_children'   => $data['total_children'] ?? 0,
                        'total_adults'     => $data['total_adults'] ?? 0,
                    ]
                );
                $updatedCount++;
            }
        });

        return $updatedCount;
    }
    /**
     * Get sitio vulnerability ranking.
     * Vulnerability score is based on the percentage of vulnerable individuals 
     * (PWDs + Seniors + Children) out of the total population.
     */
    public function getSitioVulnerabilityRanking(int $limit = 10): array
    {
        $vulnerabilities = DB::table('members')
            ->join('households', 'members.household_id', '=', 'households.id')
            ->join('addresses', 'households.address_id', '=', 'addresses.id')
            ->select(
                DB::raw('COALESCE(addresses.purok_sitio, "Unassigned Sitio") as sitio_name'),
                DB::raw('COUNT(members.id) as total_population'),
                DB::raw('SUM(CASE WHEN members.is_pwd = 1 THEN 1 ELSE 0 END) as total_pwd'),
                DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(YEAR, members.birth_date, CURDATE()) >= 60 THEN 1 ELSE 0 END) as total_seniors'),
                DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(YEAR, members.birth_date, CURDATE()) < 18 THEN 1 ELSE 0 END) as total_children')
            )
            ->groupBy(DB::raw('COALESCE(addresses.purok_sitio, "Unassigned Sitio")'))
            ->get();

        return $vulnerabilities->map(function ($item) {
            $vulnerableCount = $item->total_pwd + $item->total_seniors + $item->total_children;
            $vulnerabilityScore = $item->total_population > 0 
                ? ($vulnerableCount / $item->total_population) * 100 
                : 0;

            return [
                'sitio'               => $item->sitio_name,
                'total_population'    => $item->total_population,
                'vulnerable_count'    => $vulnerableCount,
                'vulnerability_score' => round($vulnerabilityScore, 2),
                'pwd_count'           => $item->total_pwd,
                'senior_count'        => $item->total_seniors,
                'child_count'         => $item->total_children,
            ];
        })->sortByDesc('vulnerability_score')->take($limit)->values()->all();
    }
}
