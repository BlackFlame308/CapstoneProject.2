<?php

namespace App\Services;

use App\Models\Household;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * DashboardService
 *
 * Provides high-level statistics for the admin dashboard.
 * Heavy analytics and refresh operations are delegated to AnalyticsRefreshService.
 */
class DashboardService
{
    public function __construct(
        private readonly AnalyticsRefreshService $analyticsService
    ) {}

    public function getStats(): array
    {
        $cutoffs = $this->ageCutoffs();

        return [
            'totalHouseholds' => Household::count(),
            'totalMembers'    => Member::count(),
            'totalPWD'        => Member::where('is_pwd', true)->count(),
            'totalSeniors'    => Member::whereDate('birth_date', '<=', $cutoffs['senior'])->count(),
            'totalAdults'     => Member::whereDate('birth_date', '<=', $cutoffs['adult'])
                ->whereDate('birth_date', '>', $cutoffs['senior'])
                ->count(),
            'totalChildren'   => Member::whereDate('birth_date', '>', $cutoffs['adult'])->count(),
            'totalUsers'      => User::count(),
            'totalCaptains'   => User::whereHas('role', fn ($q) => $q->where('name', 'Captain'))->count(),
        ];
    }

    public function getAgeDistribution(): array
    {
        $cutoffs = $this->ageCutoffs();

        return [
            'children' => Member::whereDate('birth_date', '>', $cutoffs['adult'])->count(),
            'adults'   => Member::whereDate('birth_date', '<=', $cutoffs['adult'])
                ->whereDate('birth_date', '>', $cutoffs['senior'])
                ->count(),
            'seniors'  => Member::whereDate('birth_date', '<=', $cutoffs['senior'])->count(),
        ];
    }

    public function getBarangayStats(): array
    {
        return $this->analyticsService->getBarangayStats($this->ageCutoffs());
    }

    public function getMembersByBarangay(): array
    {
        return DB::table('members')
            ->join('households', 'members.household_id', '=', 'households.id')
            ->join('addresses', 'households.address_id', '=', 'addresses.id')
            ->join('barangays', 'addresses.barangay_id', '=', 'barangays.id')
            ->whereNull('households.deleted_at')
            ->whereNull('members.deleted_at')
            ->groupBy('barangays.id', 'barangays.name')
            ->select('barangays.name')
            ->selectRaw('COUNT(members.id) as count')
            ->orderBy('barangays.name')
            ->get()
            ->all();
    }

    public function getRecentHouseholds(int $limit = 5): array
    {
        return Household::with(['address.barangay.city.province.region'])
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn ($household) => [
                'id'             => $household->id,
                'household_code' => $household->household_code,
                'household_name' => $household->household_name,
                'address' => [
                    'street'      => $household->address?->street,
                    'purok_sitio' => $household->address?->purok_sitio,
                    'barangay'    => $household->address?->barangay
                        ? ['name' => $household->address->barangay->name]
                        : null,
                ],
                'contact_number' => $household->contact_number,
                'member_count'   => $household->member_count,
                'population'     => $household->member_count,
                'created_at'     => $household->created_at?->toDateTimeString(),
            ])
            ->all();
    }

    public function refreshAnalytics(?array $locationIds = null): int
    {
        return $this->analyticsService->refreshAnalytics($this->ageCutoffs(), $locationIds);
    }

    public function getSitioVulnerabilityRanking(int $limit = 10): array
    {
        $cutoffs = $this->ageCutoffs();

        return DB::table('members')
            ->join('households', 'members.household_id', '=', 'households.id')
            ->join('addresses', 'households.address_id', '=', 'addresses.id')
            ->whereNull('households.deleted_at')
            ->whereNull('members.deleted_at')
            ->groupBy(DB::raw("COALESCE(addresses.purok_sitio, 'Unassigned Sitio')"))
            ->selectRaw("COALESCE(addresses.purok_sitio, 'Unassigned Sitio') as sitio_name")
            ->selectRaw('COUNT(members.id) as total_population')
            ->selectRaw('SUM(CASE WHEN members.is_pwd = 1 THEN 1 ELSE 0 END) as total_pwd')
            ->selectRaw('SUM(CASE WHEN members.birth_date <= ? THEN 1 ELSE 0 END) as total_seniors', [$cutoffs['senior']])
            ->selectRaw('SUM(CASE WHEN members.birth_date > ? THEN 1 ELSE 0 END) as total_children', [$cutoffs['adult']])
            ->get()
            ->map(fn ($item) => $this->mapSitioItem($item))
            ->sortByDesc('vulnerability_score')
            ->take($limit)
            ->values()
            ->all();
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function ageCutoffs(): array
    {
        return [
            'adult'  => now()->subYears(18)->toDateString(),
            'senior' => now()->subYears(60)->toDateString(),
        ];
    }

    private function mapSitioItem(object $item): array
    {
        $vulnerable = (int) $item->total_pwd + (int) $item->total_seniors + (int) $item->total_children;
        $total      = (int) $item->total_population;

        return [
            'sitio'               => $item->sitio_name,
            'total_population'    => $total,
            'vulnerable_count'    => $vulnerable,
            'vulnerability_score' => $total > 0 ? round(($vulnerable / $total) * 100, 2) : 0,
            'pwd_count'           => (int) $item->total_pwd,
            'senior_count'        => (int) $item->total_seniors,
            'child_count'         => (int) $item->total_children,
        ];
    }
}
