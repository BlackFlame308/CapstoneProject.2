<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ReportController
 *
 * Exposes external report endpoints (evacuation, rescue, logistics)
 * protected by a secure handshake header.
 * Each report delegates query logic to private focused methods.
 */
class ReportController extends Controller
{
    private const HANDSHAKE_HEADER = 'X-Handshake-Key';

    public function evacuation(Request $request): JsonResponse
    {
        if ($err = $this->verifyHandshake($request)) return $err;

        try {
            if (!Schema::hasTable('evacuation_records')) {
                return $this->emptySuccess();
            }

            $records = $this->queryEvacuationRecords($request)->get();

            $data = $records->map(fn($r) => [
                'id'         => $r->record_id ?? $r->id ?? null,
                'status'     => $r->status ?? null,
                'entry_date' => $r->entry_date ?? null,
                'exit_date'  => $r->exit_date ?? null,
                'event_name' => $r->event_name ?? null,
                'center_name'=> $r->center_name ?? null,
                'household'  => [
                    'code'     => $r->household_code ?? null,
                    'name'     => $r->household_name ?? null,
                    'sitio'    => $r->purok_sitio ?? null,
                    'barangay' => $r->barangay_name ?? null,
                ],
            ]);

            return response()->json(['status' => 'success', 'data' => $data]);
        } catch (\Throwable $e) {
            return $this->queryError('Evacuation report query error', $e);
        }
    }

    public function rescue(Request $request): JsonResponse
    {
        if ($err = $this->verifyHandshake($request)) return $err;

        try {
            if (!Schema::hasTable('responder_assignments')) {
                return $this->emptySuccess();
            }

            $records = $this->queryRescueRecords($request)->get();

            $data = $records->map(fn($r) => [
                'id'             => $r->assignment_id ?? $r->id ?? null,
                'status'         => $r->status ?? null,
                'assigned_at'    => $r->assigned_at ?? null,
                'completed_at'   => $r->completed_at ?? null,
                'responder_name' => $r->responder_name ?? null,
                'team' => [
                    'name' => $r->team_name ?? null,
                    'type' => $r->team_type ?? null,
                ],
            ]);

            return response()->json(['status' => 'success', 'data' => $data]);
        } catch (\Throwable $e) {
            return $this->queryError('Rescue report query error', $e);
        }
    }

    public function logistics(Request $request): JsonResponse
    {
        if ($err = $this->verifyHandshake($request)) return $err;

        try {
            if (!Schema::hasTable('resource_requests')) {
                return $this->emptySuccess();
            }

            $records = $this->queryLogisticsRecords($request)->get();

            $data = $records->map(fn($r) => [
                'id'           => $r->request_id ?? $r->id ?? null,
                'item_type'    => $r->resource_type ?? null,
                'quantity'     => $r->quantity ?? null,
                'requested_at' => $r->created_at ?? null,
                'center_name'  => $r->center_name ?? null,
                'urgency'      => $r->urgency_label ?? null,
                'status' => [
                    'label' => $r->status_label ?? null,
                    'key'   => $r->status_key ?? null,
                ],
            ]);

            return response()->json(['status' => 'success', 'data' => $data]);
        } catch (\Throwable $e) {
            return $this->queryError('Logistics report query error', $e);
        }
    }

    // ── Private query builders ────────────────────────────────────────────────

    private function queryEvacuationRecords(Request $request)
    {
        $query = DB::table('evacuation_records')
            ->leftJoin('disaster_events', 'evacuation_records.event_id', '=', 'disaster_events.event_id')
            ->leftJoin('evacuation_centers', 'evacuation_records.center_id', '=', 'evacuation_centers.evacuation_center_id')
            ->leftJoin('households', 'evacuation_records.household_id', '=', 'households.household_id')
            ->leftJoin('addresses', 'households.address_id', '=', 'addresses.address_id')
            ->select(
                'evacuation_records.*',
                'disaster_events.name as event_name',
                'evacuation_centers.name as center_name',
                'households.household_name',
                'households.household_code',
                'addresses.purok_sitio',
                'addresses.barangay_name'
            );

        if ($request->filled('date_from')) {
            $query->whereDate('evacuation_records.created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('evacuation_records.created_at', '<=', $request->date_to);
        }

        return $query->orderByDesc('evacuation_records.created_at');
    }

    private function queryRescueRecords(Request $request)
    {
        $query = DB::table('responder_assignments')
            ->leftJoin('responders', 'responder_assignments.responder_id', '=', 'responders.responder_id')
            ->leftJoin('rescue_teams', 'responder_assignments.team_id', '=', 'rescue_teams.team_id')
            ->select(
                'responder_assignments.*',
                'responders.full_name as responder_name',
                'rescue_teams.team_name',
                'rescue_teams.team_type'
            );

        if ($request->filled('status')) {
            $query->where('responder_assignments.status',
                $request->status === 'completed' ? 'completed' : '!= completed');
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

        return $query->orderByDesc('responder_assignments.assigned_at');
    }

    private function queryLogisticsRecords(Request $request)
    {
        $query = DB::table('resource_requests')
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

        return $query->orderByDesc('resource_requests.created_at');
    }

    // ── Private response helpers ──────────────────────────────────────────────

    private function verifyHandshake(Request $request): ?JsonResponse
    {
        $provided = $request->header(self::HANDSHAKE_HEADER);
        $secret   = config('app.api_handshake_key') ?: 'safetrack_handshake_secret';

        if ($provided !== $secret) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Handshake verification failed: Invalid or missing X-Handshake-Key header.',
            ], 401);
        }

        return null;
    }

    private function emptySuccess(): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => []]);
    }

    private function queryError(string $context, \Throwable $e): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => "{$context}: " . $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ], 500);
    }
}
