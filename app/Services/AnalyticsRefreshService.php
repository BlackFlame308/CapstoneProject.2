<?php

namespace App\Services;

use App\Models\Analytics;
use App\Models\Barangay;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

/**
 * AnalyticsRefreshService
 *
 * Handles barangay-level statistics aggregation and analytics refresh.
 * Separated from DashboardService to keep each service under 200 lines.
 */
class AnalyticsRefreshService
{
    /** @param string[] $ageCutoffs Must contain 'adult' and 'senior' date strings */
    public function getBarangayStats(array $ageCutoffs): array
    {
        $barangays      = Barangay::with(['city.province.region'])->orderBy('name')->get();
        $householdCounts = $this->fetchHouseholdCounts();
        $memberCounts    = $this->fetchMemberCounts($ageCutoffs);

        return $barangays->map(function ($barangay) use ($householdCounts, $memberCounts) {
            $stats = $memberCounts->get($barangay->id);

            return [
                'id'               => $barangay->id,
                'name'             => $barangay->name,
                'city_name'        => $barangay->city?->name ?? '',
                'province_name'    => $barangay->city?->province?->name ?? '',
                'region_name'      => $barangay->city?->province?->region?->name ?? '',
                'households_count' => (int) ($householdCounts[$barangay->id] ?? 0),
                'total_population' => (int) ($stats->total_population ?? 0),
                'total_males'      => (int) ($stats->total_males ?? 0),
                'total_females'    => (int) ($stats->total_females ?? 0),
                'total_pwd'        => (int) ($stats->total_pwd ?? 0),
                'total_pregnant'   => (int) ($stats->total_pregnant ?? 0),
                'total_seniors'    => (int) ($stats->total_seniors ?? 0),
                'total_children'   => (int) ($stats->total_children ?? 0),
                'total_adults'     => (int) ($stats->total_adults ?? 0),
            ];
        })->all();
    }

    public function refreshAnalytics(array $ageCutoffs, ?array $locationIds = null): int
    {
        $period       = now()->startOfMonth()->toDateString();
        $updatedCount = 0;

        DB::transaction(function () use ($period, $ageCutoffs, $locationIds, &$updatedCount) {
            $householdCounts = $this->fetchHouseholdCountsByPurok($locationIds);
            $memberStats     = $this->fetchMemberStatsByPurok($ageCutoffs, $locationIds);

            $householdCounts->keys()
                ->merge($memberStats->keys())
                ->unique()
                ->each(function ($key) use ($householdCounts, $memberStats, $period, &$updatedCount) {
                    $householdRow = $householdCounts->get($key);
                    $memberRow    = $memberStats->get($key);
                    $barangayId   = $householdRow?->barangay_id ?? $memberRow?->barangay_id;

                    if (!$barangayId) return;

                    Analytics::updateOrCreate(
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

    // ── Private query helpers ─────────────────────────────────────────────────

    private function fetchHouseholdCounts(): \Illuminate\Support\Collection
    {
        return DB::table('households')
            ->join('addresses', 'households.address_id', '=', 'addresses.id')
            ->whereNotNull('addresses.barangay_id')
            ->whereNull('households.deleted_at')
            ->groupBy('addresses.barangay_id')
            ->select('addresses.barangay_id')
            ->selectRaw('COUNT(households.id) as count')
            ->pluck('count', 'barangay_id');
    }

    private function fetchMemberCounts(array $cutoffs): \Illuminate\Support\Collection
    {
        return DB::table('members')
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
    }

    private function fetchHouseholdCountsByPurok(?array $locationIds): \Illuminate\Support\Collection
    {
        return DB::table('households')
            ->join('addresses', 'households.address_id', '=', 'addresses.id')
            ->whereNotNull('addresses.barangay_id')
            ->whereNull('households.deleted_at')
            ->when($locationIds, fn($q) => $q->whereIn('addresses.barangay_id', $locationIds))
            ->groupBy('addresses.barangay_id', 'addresses.purok_sitio')
            ->select('addresses.barangay_id', 'addresses.purok_sitio')
            ->selectRaw('COUNT(households.id) as total_households')
            ->get()
            ->keyBy(fn($row) => $row->barangay_id . '|' . ($row->purok_sitio ?? ''));
    }

    private function fetchMemberStatsByPurok(array $cutoffs, ?array $locationIds): \Illuminate\Support\Collection
    {
        return DB::table('members')
            ->join('households', 'members.household_id', '=', 'households.id')
            ->join('addresses', 'households.address_id', '=', 'addresses.id')
            ->whereNotNull('addresses.barangay_id')
            ->whereNull('households.deleted_at')
            ->whereNull('members.deleted_at')
            ->when($locationIds, fn($q) => $q->whereIn('addresses.barangay_id', $locationIds))
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
            ->keyBy(fn($row) => $row->barangay_id . '|' . ($row->purok_sitio ?? ''));
    }
}
