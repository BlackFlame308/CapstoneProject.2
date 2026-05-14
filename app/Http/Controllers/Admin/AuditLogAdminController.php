<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * AuditLogAdminController
 * Manages audit logging for tracking system changes
 */
class AuditLogAdminController extends Controller
{
    /**
     * List audit logs with filtering
     */
    public function index(Request $request)
    {
        $query = \DB::table('audit_logs');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by model
        if ($request->filled('model')) {
            $query->where('model', $request->model);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        $logs = $query->latest()->paginate(50)->withQueryString();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'filters' => $request->only(['user_id', 'action', 'model', 'start_date', 'end_date']),
        ]);
    }

    /**
     * View detailed log entry
     */
    public function show($id)
    {
        $log = \DB::table('audit_logs')->find($id);

        if (!$log) {
            abort(404, 'Audit log not found');
        }

        return view('admin.audit-logs.show', [
            'log' => $log,
        ]);
    }

    /**
     * Clear old logs (keep last 6 months)
     */
    public function clearOldLogs()
    {
        $deleted = \DB::table('audit_logs')
            ->where('created_at', '<', now()->subMonths(6))
            ->delete();

        return back()->with('success', "Deleted $deleted old audit log entries.");
    }
}
