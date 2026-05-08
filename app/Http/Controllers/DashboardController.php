<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAnalyticsRequest;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    public function index()
    {
        return Inertia::render('Dashboard/Index', [
            'stats'            => $this->dashboardService->getStats(),
            'barangayStats'    => $this->dashboardService->getBarangayStats(),
            'recentHouseholds' => $this->dashboardService->getRecentHouseholds(),
            'membersByBarangay'=> $this->dashboardService->getMembersByBarangay(),
            'ageDistribution'  => $this->dashboardService->getAgeDistribution(),
            'sitioVulnerability' => $this->dashboardService->getSitioVulnerabilityRanking(),
        ]);
    }

    public function updateAnalytics(UpdateAnalyticsRequest $request)
    {
        $locationIds = $request->input('location_ids');
        $updatedCount = $this->dashboardService->refreshAnalytics($locationIds);

        return back()->with('success', "Analytics updated for {$updatedCount} locations.");
    }
}
