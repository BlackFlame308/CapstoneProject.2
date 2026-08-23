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

    public function storeEvacuation(Request $request): JsonResponse
    {
        if ($err = $this->verifyHandshake($request)) return $err;

        $validated = $request->validate([
            'household_id'    => 'nullable|string',
            'household_code'  => 'nullable|string',
            'event_id'        => 'nullable|integer',
            'center_id'       => 'nullable|integer',
            'evacuated_count' => 'nullable|integer',
            'status'          => 'nullable|string|max:50',
        ]);

        try {
            if (!Schema::hasTable('evacuation_records')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Table evacuation_records does not exist.',
                ], 400);
            }

            $householdId = $validated['household_id'] ?? null;
            if (!$householdId && !empty($validated['household_code'])) {
                $householdId = DB::table('households')->where('household_code', $validated['household_code'])->value('household_id');
            }

            $id = DB::table('evacuation_records')->insertGetId([
                'status'          => $validated['status'] ?? 'active',
                'event_id'        => $validated['event_id'] ?? null,
                'center_id'       => $validated['center_id'] ?? null,
                'household_id'    => $householdId ?? 'N/A',
                'evacuated_count' => $validated['evacuated_count'] ?? 1,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Evacuation report stored successfully.',
                'id'      => $id,
            ], 201);
        } catch (\Throwable $e) {
            return $this->queryError('Store evacuation report error', $e);
        }
    }

    public function storeRescue(Request $request): JsonResponse
    {
        if ($err = $this->verifyHandshake($request)) return $err;

        $validated = $request->validate([
            'responder_id' => 'nullable|integer',
            'team_id'      => 'nullable|integer',
            'status'       => 'nullable|string|max:50',
            'assigned_at'  => 'nullable|date',
            'completed_at' => 'nullable|date',
        ]);

        try {
            if (!Schema::hasTable('responder_assignments')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Table responder_assignments does not exist.',
                ], 400);
            }

            $id = DB::table('responder_assignments')->insertGetId([
                'status'       => $validated['status'] ?? 'assigned',
                'assigned_at'  => $validated['assigned_at'] ?? now(),
                'completed_at' => $validated['completed_at'] ?? null,
                'responder_id' => $validated['responder_id'] ?? null,
                'team_id'      => $validated['team_id'] ?? null,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Rescue report stored successfully.',
                'id'      => $id,
            ], 201);
        } catch (\Throwable $e) {
            return $this->queryError('Store rescue report error', $e);
        }
    }

    public function storeLogistics(Request $request): JsonResponse
    {
        if ($err = $this->verifyHandshake($request)) return $err;

        $validated = $request->validate([
            'resource_type'        => 'required|string|max:255',
            'quantity'             => 'required|integer|min:1',
            'evacuation_center_id' => 'nullable|integer',
            'urgency_id'           => 'nullable|integer',
            'status_id'            => 'nullable|integer',
        ]);

        try {
            if (!Schema::hasTable('resource_requests')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Table resource_requests does not exist.',
                ], 400);
            }

            $id = DB::table('resource_requests')->insertGetId([
                'resource_type'        => $validated['resource_type'],
                'quantity'             => $validated['quantity'],
                'evacuation_center_id' => $validated['evacuation_center_id'] ?? null,
                'urgency_id'           => $validated['urgency_id'] ?? null,
                'status_id'            => $validated['status_id'] ?? null,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Logistics report stored successfully.',
                'id'      => $id,
            ], 201);
        } catch (\Throwable $e) {
            return $this->queryError('Store logistics report error', $e);
        }
    }

    // ── Private query builders ────────────────────────────────────────────────

    private function queryEvacuationRecords(Request $request)
    {
        $query = DB::table('evacuation_records');

        if (Schema::hasTable('disaster_events')) {
            $query->leftJoin('disaster_events', 'evacuation_records.event_id', '=', 'disaster_events.event_id')
                  ->addSelect('disaster_events.name as event_name');
        } else {
            $query->selectRaw('NULL as event_name');
        }

        if (Schema::hasTable('evacuation_centers')) {
            $query->leftJoin('evacuation_centers', 'evacuation_records.center_id', '=', 'evacuation_centers.evacuation_center_id')
                  ->addSelect('evacuation_centers.name as center_name');
        } else {
            $query->selectRaw('NULL as center_name');
        }

        if (Schema::hasTable('households')) {
            $query->leftJoin('households', 'evacuation_records.household_id', '=', 'households.household_id')
                  ->addSelect('households.household_name', 'households.household_code');

            if (Schema::hasTable('addresses')) {
                $query->leftJoin('addresses', 'households.address_id', '=', 'addresses.address_id')
                      ->addSelect('addresses.purok_sitio');

                if (Schema::hasTable('barangays')) {
                    $query->leftJoin('barangays', 'addresses.barangay_id', '=', 'barangays.barangay_id');
                    if (Schema::hasColumn('barangays', 'barangay_name')) {
                        $query->addSelect('barangays.barangay_name as barangay_name');
                    } elseif (Schema::hasColumn('barangays', 'name')) {
                        $query->addSelect('barangays.name as barangay_name');
                    } else {
                        $query->selectRaw('NULL as barangay_name');
                    }
                } else {
                    $query->selectRaw('NULL as barangay_name');
                }
            } else {
                $query->selectRaw('NULL as purok_sitio', 'NULL as barangay_name');
            }
        } else {
            $query->selectRaw('NULL as household_name', 'NULL as household_code', 'NULL as purok_sitio', 'NULL as barangay_name');
        }

        $query->addSelect('evacuation_records.*');

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
        $query = DB::table('responder_assignments');

        if (Schema::hasTable('responders')) {
            $query->leftJoin('responders', 'responder_assignments.responder_id', '=', 'responders.responder_id')
                  ->addSelect('responders.full_name as responder_name');
        } else {
            $query->selectRaw('NULL as responder_name');
        }

        if (Schema::hasTable('rescue_teams')) {
            $query->leftJoin('rescue_teams', 'responder_assignments.team_id', '=', 'rescue_teams.team_id')
                  ->addSelect('rescue_teams.team_name', 'rescue_teams.team_type');
        } else {
            $query->selectRaw('NULL as team_name', 'NULL as team_type');
        }

        $query->addSelect('responder_assignments.*');

        if ($request->filled('status')) {
            $query->where('responder_assignments.status',
                $request->status === 'completed' ? 'completed' : '!= completed');
        }
        if ($request->filled('incident_type') && Schema::hasTable('rescue_teams')) {
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
        $query = DB::table('resource_requests');

        if (Schema::hasTable('evacuation_centers')) {
            $query->leftJoin('evacuation_centers', 'resource_requests.evacuation_center_id', '=', 'evacuation_centers.evacuation_center_id')
                  ->addSelect('evacuation_centers.name as center_name');
        } else {
            $query->selectRaw('NULL as center_name');
        }

        if (Schema::hasTable('urgency_levels')) {
            $query->leftJoin('urgency_levels', 'resource_requests.urgency_id', '=', 'urgency_levels.urgency_id')
                  ->addSelect('urgency_levels.urgency_label');
        } else {
            $query->selectRaw('NULL as urgency_label');
        }

        if (Schema::hasTable('resource_request_status')) {
            $query->leftJoin('resource_request_status', 'resource_requests.status_id', '=', 'resource_request_status.status_id')
                  ->addSelect('resource_request_status.status_label', 'resource_request_status.status_key');
        } else {
            $query->selectRaw('NULL as status_label', 'NULL as status_key');
        }

        $query->addSelect('resource_requests.*');

        if ($request->filled('status') && Schema::hasTable('resource_request_status')) {
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
