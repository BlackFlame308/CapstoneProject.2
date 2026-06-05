<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * ReportAdminController
 * 
 * Displays reports from subsystems
 * 
 * FEATURES:
 * - List all reports
 * - Filter by report type:
 *   - Evacuation Reports
 *   - Rescue Reports
 *   - Logistics Reports
 * - Filter by source module
 * - Display placeholder for missing subsystem data
 * 
 * PLACEHOLDER DATA:
 * Currently showing "No data available" messages
 * 
 * TODO FOR STUDENT:
 * - API integration points will be added here
 * - Create API endpoints for:
 *   - GET /api/reports/evacuation
 *   - GET /api/reports/rescue
 *   - GET /api/reports/logistics
 * - Implement token-based authentication (JWT or Bearer)
 * - Add error handling for API failures
 * - Implement caching to reduce API calls
 * - Add real-time updates (WebSocket or polling)
 */
class ReportAdminController extends Controller
{
    /**
     * Show all reports (mixed types)
     */
    public function index(Request $request)
    {
        $reportType = $request->get('type', 'all');
        
        $evacuationCount = \Illuminate\Support\Facades\Schema::hasTable('evacuation_records')
            ? \Illuminate\Support\Facades\DB::table('evacuation_records')->count()
            : 0;
            
        $rescueCount = \Illuminate\Support\Facades\Schema::hasTable('responder_assignments')
            ? \Illuminate\Support\Facades\DB::table('responder_assignments')->count()
            : 0;
            
        $logisticsCount = \Illuminate\Support\Facades\Schema::hasTable('resource_requests')
            ? \Illuminate\Support\Facades\DB::table('resource_requests')->count()
            : 0;
        
        return view('admin.reports.index', [
            'evacuationCount' => $evacuationCount,
            'rescueCount' => $rescueCount,
            'logisticsCount' => $logisticsCount,
            'reportType' => $reportType,
        ]);
    }

    /**
     * Show evacuation reports
     */
    public function evacuation(Request $request)
    {
        $filters = $request->only(['status', 'date_from', 'date_to']);
        
        if (!\Illuminate\Support\Facades\Schema::hasTable('evacuation_records')) {
            return view('admin.reports.evacuation', [
                'reports' => collect([]),
                'filters' => $filters,
            ]);
        }
        
        $query = \Illuminate\Support\Facades\DB::table('evacuation_records')
            ->leftJoin('disaster_events', 'evacuation_records.event_id', '=', 'disaster_events.event_id')
            ->leftJoin('evacuation_centers', 'evacuation_records.center_id', '=', 'evacuation_centers.evacuation_center_id')
            ->leftJoin('households', 'evacuation_records.household_id', '=', 'households.household_id')
            ->select(
                'evacuation_records.*',
                'disaster_events.name as event_name',
                'evacuation_centers.name as center_name',
                'households.household_name'
            );

        if ($request->filled('date_from')) {
            $query->whereDate('evacuation_records.created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('evacuation_records.created_at', '<=', $request->date_to);
        }

        $evacuationReports = $query->orderByDesc('evacuation_records.created_at')->get();
        
        return view('admin.reports.evacuation', [
            'reports' => $evacuationReports,
            'filters' => $filters,
        ]);
    }

    /**
     * Show rescue reports
     */
    public function rescue(Request $request)
    {
        $filters = $request->only(['status', 'incident_type', 'date_from', 'date_to']);
        
        if (!\Illuminate\Support\Facades\Schema::hasTable('responder_assignments')) {
            return view('admin.reports.rescue', [
                'reports' => collect([]),
                'filters' => $filters,
            ]);
        }
        
        $query = \Illuminate\Support\Facades\DB::table('responder_assignments')
            ->leftJoin('responders', 'responder_assignments.responder_id', '=', 'responders.responder_id')
            ->leftJoin('rescue_teams', 'responder_assignments.team_id', '=', 'rescue_teams.team_id')
            ->select(
                'responder_assignments.*',
                'responders.full_name as responder_name',
                'rescue_teams.team_name',
                'rescue_teams.team_type'
            );

        if ($request->filled('status')) {
            if ($request->status === 'completed') {
                $query->where('responder_assignments.status', 'completed');
            } else {
                $query->where('responder_assignments.status', '!=', 'completed');
            }
        }
        if ($request->filled('incident_type')) {
            $query->where('rescue_teams.team_type', $request->incident_type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('responder_assignments.assigned_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('responder_assignments.assigned_at', '<=', $request->date_to);
        }

        $rescueReports = $query->orderByDesc('responder_assignments.assigned_at')->get();
        
        return view('admin.reports.rescue', [
            'reports' => $rescueReports,
            'filters' => $filters,
        ]);
    }

    /**
     * Show logistics reports
     */
    public function logistics(Request $request)
    {
        $filters = $request->only(['status', 'item_type', 'date_from', 'date_to']);
        
        if (!\Illuminate\Support\Facades\Schema::hasTable('resource_requests')) {
            return view('admin.reports.logistics', [
                'reports' => collect([]),
                'filters' => $filters,
            ]);
        }
        
        $query = \Illuminate\Support\Facades\DB::table('resource_requests')
            ->leftJoin('evacuation_centers', 'resource_requests.evacuation_center_id', '=', 'evacuation_centers.evacuation_center_id')
            ->leftJoin('urgency_levels', 'resource_requests.urgency_id', '=', 'urgency_levels.urgency_id')
            ->leftJoin('resource_request_status', 'resource_requests.status_id', '=', 'resource_request_status.status_id')
            ->select(
                'resource_requests.*',
                'evacuation_centers.name as center_name',
                'urgency_levels.urgency_label',
                'resource_request_status.status_label',
                'resource_request_status.status_key'
            );

        if ($request->filled('status')) {
            $query->where('resource_request_status.status_key', $request->status);
        }
        if ($request->filled('item_type')) {
            $query->where('resource_requests.resource_type', 'like', '%' . $request->item_type . '%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('resource_requests.created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('resource_requests.created_at', '<=', $request->date_to);
        }

        $logisticsReports = $query->orderByDesc('resource_requests.created_at')->get();
        
        return view('admin.reports.logistics', [
            'reports' => $logisticsReports,
            'filters' => $filters,
        ]);
    }
}
