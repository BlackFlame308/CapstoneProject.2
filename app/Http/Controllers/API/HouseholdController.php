<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreHouseholdApiRequest;
use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use App\Services\HouseholdApiService;
use App\Services\HouseholdCsvImportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * API HouseholdController
 * 
 * Exposes REST API endpoints for managing households, including CSV uploads, CRUD,
 * and location-based filtering.
 * 
 * CLEAN CODE DELEGATION STRUCTURE:
 * 1. Filtering & Complex Creation Operations: Delegated to Services\HouseholdApiService
 *    (handles mapping index filters and creating household-related address, user, and member models).
 * 2. Request Validation: Delegated to FormRequest StoreHouseholdApiRequest.
 */
class HouseholdController extends Controller
{
    public function __construct(
        private readonly HouseholdApiService $apiService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Household::with(['address.barangay.city.province.region', 'members']);

            $this->apiService->applyIndexFilters($query, $request);

            return response()->json([
                'status' => 'success',
                'data'   => $query->latest()->paginate(15),
            ]);
        } catch (\Exception $e) {
            \Log::error('API Household index error: ' . $e->getMessage());
            return $this->serverError('Failed to load households');
        }
    }

    public function store(StoreHouseholdApiRequest $request): JsonResponse
    {
        try {
            $validated     = $request->validated();
            $householdRole = Role::where('name', 'Household')->first();

            if (!$householdRole) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Household role not configured. Run seeders.',
                ], 500);
            }

            $household = DB::transaction(
                fn() => $this->apiService->createHouseholdWithMembers($validated, $request->user(), $householdRole)
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Household created successfully',
                'data'    => $household->load(['address.barangay.city.province.region', 'members']),
            ], 201);
        } catch (\Exception $e) {
            \Log::error('API Household store error: ' . $e->getMessage());
            return $this->serverError('Failed to create household');
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $household = Household::with(['address.barangay.city.province.region', 'members'])->findOrFail($id);
            return response()->json(['status' => 'success', 'data' => $household]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Household not found');
        } catch (\Exception $e) {
            \Log::error('API Household show error: ' . $e->getMessage());
            return $this->serverError('Failed to load household');
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $household = Household::with('address')->findOrFail($id);
            $validated = $request->validate([
                'household_name'    => 'nullable|string|max:100',
                'email'             => 'nullable|email|max:150|unique:households,email,' . $household->id,
                'street'            => 'nullable|string|max:255',
                'purok_sitio'       => 'nullable|string|max:150',
                'full_address'      => 'nullable|string|max:500',
                'contact_number'    => 'nullable|string|max:50',
                'emergency_contact' => 'nullable|string|max:50',
            ]);

            DB::transaction(function () use ($household, $validated) {
                $household->address?->update([
                    'street'       => $validated['street']       ?? $household->address->street,
                    'purok_sitio'  => $validated['purok_sitio']  ?? $household->address->purok_sitio,
                    'full_address' => $validated['full_address'] ?? $household->address->full_address,
                ]);
                $household->update([
                    'household_name'    => $validated['household_name']    ?? $household->household_name,
                    'email'             => $validated['email']             ?? $household->email,
                    'contact_number'    => $validated['contact_number']    ?? $household->contact_number,
                    'emergency_contact' => $validated['emergency_contact'] ?? $household->emergency_contact,
                ]);
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Household updated successfully',
                'data'    => $household->fresh()->load(['address.barangay.city.province.region', 'members']),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Household not found');
        } catch (\Exception $e) {
            \Log::error('API Household update error: ' . $e->getMessage());
            return $this->serverError('Failed to update household');
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $household = Household::with('address')->findOrFail($id);
            DB::transaction(function () use ($household) {
                $household->members()->delete();
                User::where('household_id', $household->id)->delete();
                $household->address?->delete();
                $household->delete();
            });
            return response()->json(['status' => 'success', 'message' => 'Household deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Household not found');
        } catch (\Exception $e) {
            \Log::error('API Household destroy error: ' . $e->getMessage());
            return $this->serverError('Failed to delete household');
        }
    }

    public function uploadCsv(Request $request): JsonResponse
    {
        try {
            $request->validate(['csv_file' => 'required|file|mimes:csv,txt|max:10240']);

            $result = (new HouseholdCsvImportService())
                ->import($request->file('csv_file')->getRealPath(), $request->user()->id);

            return response()->json([
                'status'  => 'success',
                'message' => $result['message'],
                'stats'   => $result['stats'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'validation_error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('API CSV upload error: ' . $e->getMessage());
            return $this->serverError('CSV import failed. Please check the file format and try again.');
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function notFound(string $message): JsonResponse
    {
        return response()->json(['status' => 'not_found', 'message' => $message], 404);
    }

    private function serverError(string $message): JsonResponse
    {
        return response()->json(['status' => 'error', 'message' => $message], 500);
    }
}
