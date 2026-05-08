<?php

namespace App\Services;

use App\Models\Analytic;
use App\Models\Barangay;
use App\Models\Household;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
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
            'adults'  => Member::whereDate('birth_date', '<=', $cutoffs['adult'])
                ->whereDate('birth_date', '>', $cutoffs['senior'])
                ->count(),
            'seniors' => Member::whereDate('birth_date', '<=', $cutoffs['senior'])->count(),
        ];
    }

    public function getBarangayStats(): array
    {
        $cutoffs = $this->ageCutoffs();
        $barangays = Barangay::with(['city.province.region'])->orderBy('name')->get();

        $householdCounts = DB::table('households')
            ->join('addresses', 'households.address_id', '=', 'addresses.id')
            ->whereNotNull('addresses.barangay_id')
            ->whereNull('households.deleted_at')
            ->groupBy('addresses.barangay_id')
            ->select('addresses.barangay_id')
            ->selectRaw('COUNT(households.id) as count')
            ->pluck('count', 'barangay_id')
            ->toArray();

        $memberCounts = DB::table('members')
            ->join('households', 'members.household_id', '=', 'households.id')
            ->join('addresses', 'households.address_id', '=', 'addresses.id')
            ->whereNotNull('addresses.barangay_id')
            ->whereNull('households.deleted_at')
            ->whereNull('members.deleted_at')
            ->groupBy('addresses.barangay_id')
            ->select('addresses.barangay_id')
            ->selectRaw('COUNT(members.id) as total_population')
            ->selectRaw("SUM(CASE WHEN UPPER(members.sex) = 'M' THEN 1 ELSE 0 END) as total_males")
            ->selectRaw("SUM(CASE WHEN UPPER(members.sex) = 'F' THEN 1 ELSE 0 END) as total_females")
            ->selectRaw('SUM(CASE WHEN members.is_pwd = 1 THEN 1 ELSE 0 END) as total_pwd')
            ->selectRaw('SUM(CASE WHEN members.is_pregnant = 1 THEN 1 ELSE 0 END) as total_pregnant')
            ->selectRaw('SUM(CASE WHEN members.birth_date <= ? THEN 1 ELSE 0 END) as total_seniors', [$cutoffs['senior']])
            ->selectRaw('SUM(CASE WHEN members.birth_date > ? THEN 1 ELSE 0 END) as total_children', [$cutoffs['adult']])
            ->selectRaw(
                'SUM(CASE WHEN members.birth_date <= ? AND members.birth_date > ? THEN 1 ELSE 0 END) as total_adults',
                [$cutoffs['adult'], $cutoffs['senior']]
            )
            ->get()
            ->keyBy('barangay_id');

        return $barangays->map(function ($barangay) use ($householdCounts, $memberCounts) {
            $stats = $memberCounts->get($barangay->id);

            return [
                'id'                => $barangay->id,
                'name'              => $barangay->name,
                'city_name'         => $barangay->city?->name ?? '',
                'province_name'     => $barangay->city?->province?->name ?? '',
                'region_name'       => $barangay->city?->province?->region?->name ?? '',
                'households_count'  => (int) ($householdCounts[$barangay->id] ?? 0),
                'total_population'  => (int) ($stats->total_population ?? 0),
                'total_males'       => (int) ($stats->total_males ?? 0),
                'total_females'     => (int) ($stats->total_females ?? 0),
                'total_pwd'         => (int) ($stats->total_pwd ?? 0),
                'total_pregnant'    => (int) ($stats->total_pregnant ?? 0),
                'total_seniors'     => (int) ($stats->total_seniors ?? 0),
                'total_children'    => (int) ($stats->total_children ?? 0),
                'total_adults'      => (int) ($stats->total_adults ?? 0),
            ];
        })->all();
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
                'address'        => [
                    'street'      => $household->address?->street,
                    'purok_sitio' => $household->address?->purok_sitio,
                    'barangay'    => $household->address?->barangay ? [
                        'name' => $household->address->barangay->name,
                    ] : null,
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
        $period = now()->startOfMonth()->toDateString();
        $cutoffs = $this->ageCutoffs();
        $updatedCount = 0;

        DB::transaction(function () use ($period, $cutoffs, &$updatedCount, $locationIds) {
            $householdCounts = DB::table('households')
                ->join('addresses', 'households.address_id', '=', 'addresses.id')
                ->whereNotNull('addresses.barangay_id')
                ->whereNull('households.deleted_at')
                ->when($locationIds, fn ($query) => $query->whereIn('addresses.barangay_id', $locationIds))
                ->groupBy('addresses.barangay_id', 'addresses.purok_sitio')
                ->select('addresses.barangay_id', 'addresses.purok_sitio')
                ->selectRaw('COUNT(households.id) as total_households')
                ->get()
                ->keyBy(fn ($row) => $this->analyticsKey($row->barangay_id, $row->purok_sitio));

            $memberStats = DB::table('members')
                ->join('households', 'members.household_id', '=', 'households.id')
                ->join('addresses', 'households.address_id', '=', 'addresses.id')
                ->whereNotNull('addresses.barangay_id')
                ->whereNull('households.deleted_at')
                ->whereNull('members.deleted_at')
                ->when($locationIds, fn ($query) => $query->whereIn('addresses.barangay_id', $locationIds))
                ->groupBy('addresses.barangay_id', 'addresses.purok_sitio')
                ->select('addresses.barangay_id', 'addresses.purok_sitio')
                ->selectRaw('COUNT(members.id) as total_population')
                ->selectRaw("SUM(CASE WHEN UPPER(members.sex) = 'M' THEN 1 ELSE 0 END) as total_males")
                ->selectRaw("SUM(CASE WHEN UPPER(members.sex) = 'F' THEN 1 ELSE 0 END) as total_females")
                ->selectRaw('SUM(CASE WHEN members.is_pwd = 1 THEN 1 ELSE 0 END) as total_pwd')
                ->selectRaw('SUM(CASE WHEN members.is_pregnant = 1 THEN 1 ELSE 0 END) as total_pregnant')
                ->selectRaw('SUM(CASE WHEN members.birth_date <= ? THEN 1 ELSE 0 END) as total_seniors', [$cutoffs['senior']])
                ->selectRaw('SUM(CASE WHEN members.birth_date > ? THEN 1 ELSE 0 END) as total_children', [$cutoffs['adult']])
                ->selectRaw(
                    'SUM(CASE WHEN members.birth_date <= ? AND members.birth_date > ? THEN 1 ELSE 0 END) as total_adults',
                    [$cutoffs['adult'], $cutoffs['senior']]
                )
                ->get()
                ->keyBy(fn ($row) => $this->analyticsKey($row->barangay_id, $row->purok_sitio));

            $householdCounts->keys()->merge($memberStats->keys())->unique()->each(function ($key) use (
                $householdCounts,
                $memberStats,
                $period,
                &$updatedCount
            ) {
                $householdRow = $householdCounts->get($key);
                $memberRow = $memberStats->get($key);
                $barangayId = $householdRow?->barangay_id ?? $memberRow?->barangay_id;

                if (!$barangayId) {
                    return;
                }

                Analytic::updateOrCreate(
                    [
                        'barangay_id'   => $barangayId,
                        'purok_sitio'   => $householdRow?->purok_sitio ?? $memberRow?->purok_sitio,
                        'record_period' => $period,
                    ],
                    [
                        'total_households' => (int) ($householdRow->total_households ?? 0),
                        'total_population' => (int) ($memberRow->total_population ?? 0),
                        'total_males'      => (int) ($memberRow->total_males ?? 0),
                        'total_females'    => (int) ($memberRow->total_females ?? 0),
                        'total_pwd'        => (int) ($memberRow->total_pwd ?? 0),
                        'total_seniors'    => (int) ($memberRow->total_seniors ?? 0),
                        'total_children'   => (int) ($memberRow->total_children ?? 0),
                        'total_adults'     => (int) ($memberRow->total_adults ?? 0),
                        'total_pregnant'   => (int) ($memberRow->total_pregnant ?? 0),
                    ]
                );

                $updatedCount++;
            });
        });

        return $updatedCount;
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
            ->map(function ($item) {
                $vulnerableCount = (int) $item->total_pwd + (int) $item->total_seniors + (int) $item->total_children;
                $total = (int) $item->total_population;

                return [
                    'sitio'                => $item->sitio_name,
                    'total_population'     => $total,
                    'vulnerable_count'     => $vulnerableCount,
                    'vulnerability_score'  => $total > 0 ? round(($vulnerableCount / $total) * 100, 2) : 0,
                    'pwd_count'            => (int) $item->total_pwd,
                    'senior_count'         => (int) $item->total_seniors,
                    'child_count'          => (int) $item->total_children,
                ];
            })
            ->sortByDesc('vulnerability_score')
            ->take($limit)
            ->values()
            ->all();
    }

    private function ageCutoffs(): array
    {
        return [
            'adult'  => now()->subYears(18)->toDateString(),
            'senior' => now()->subYears(60)->toDateString(),
        ];
    }

    private function analyticsKey(string $barangayId, ?string $purokSitio): string
    {
        return $barangayId . '|' . ($purokSitio ?? '');
    }
}
