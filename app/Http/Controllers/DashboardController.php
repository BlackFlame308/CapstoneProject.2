<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAnalyticsRequest;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    public function index()
    {
        return redirect()->route('admin.dashboard');
    }

    public function updateAnalytics(UpdateAnalyticsRequest $request)
    {
        $locationIds = $request->input('location_ids');
        $updatedCount = $this->dashboardService->refreshAnalytics($locationIds);

        return back()->with('success', "Analytics updated for {$updatedCount} locations.");
    }
}
