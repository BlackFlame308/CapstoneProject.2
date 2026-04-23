<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Household;
use App\Models\Member;
use App\Models\Address;
use App\Models\Role;
use App\Models\User;
use App\Services\HouseholdCsvImportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class HouseholdController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $households = Household::with(['address.barangay.city.province.region', 'members'])->paginate(15);

            return response()->json([
                'status' => 'success',
                'data' => $households,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('API Household index error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load households',
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'street' => 'nullable|string|max:255',
                'purok' => 'nullable|string|max:100',
                'barangay_id' => 'required|exists:barangays,id',
                'contact_number' => 'nullable|string|max:50',
                'emergency_contact' => 'nullable|string|max:50',
                'head_first_name' => 'required|string|max:100',
                'head_middle_name' => 'nullable|string|max:100',
                'head_last_name' => 'required|string|max:100',
                'members' => 'nullable|array',
                'members.*.first_name' => 'required_with:members|string|max:100',
                'members.*.last_name' => 'required_with:members|string|max:100',
                'members.*.birth_date' => 'required_with:members|date',
                'members.*.sex' => 'required_with:members|in:M,F',
                'members.*.civil_status' => 'nullable|string|max:50',
                'members.*.education_level' => 'nullable|string|max:100',
                'members.*.profession' => 'nullable|string|max:100',
                'members.*.is_pwd' => 'nullable|boolean',
            ]);

            $householdRole = Role::where('name', 'Household')->first() ?? Role::where('id', 3)->first();
            if (!$householdRole) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Household role not configured',
                ], 500);
            }

            $address = Address::create([
                'street' => $validated['street'] ?? null,
                'purok' => $validated['purok'] ?? null,
                'barangay_id' => $validated['barangay_id'],
            ]);

            $householdCode = 'HH-' . strtoupper(Str::random(8));

            $household = Household::create([
                'household_code' => $householdCode,
                'address_id' => $address->id,
                'contact_number' => $validated['contact_number'] ?? null,
                'emergency_contact' => $validated['emergency_contact'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            $userEmail = $this->generateUniqueEmail($validated['head_first_name'], $validated['head_last_name']);
            $tempPassword = 'Temp_' . Str::random(8);

            $household->user()->create([
                'name' => $validated['head_first_name'] . ' ' . $validated['head_last_name'],
                'email' => $userEmail,
                'password' => bcrypt($tempPassword),
                'role_id' => $householdRole->id,
                'household_id' => $household->id,
                'must_change_password' => true,
            ]);

            if (!empty($validated['members'])) {
                foreach ($validated['members'] as $memberData) {
                    Member::create([
                        'household_id' => $household->id,
                        'first_name' => $memberData['first_name'],
                        'middle_name' => $memberData['middle_name'] ?? null,
                        'last_name' => $memberData['last_name'],
                        'birth_date' => $memberData['birth_date'],
                        'sex' => $memberData['sex'],
                        'civil_status' => $memberData['civil_status'] ?? null,
                        'education_level' => $memberData['education_level'] ?? null,
                        'profession' => $memberData['profession'] ?? null,
                        'is_pwd' => $memberData['is_pwd'] ?? false,
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Household created successfully',
                'data' => $household->load(['address.barangay.city.province.region', 'members']),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('API Household store error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create household',
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $household = Household::with(['address.barangay.city.province.region', 'members'])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $household,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Household not found',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('API Household show error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load household',
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $household = Household::with('address')->findOrFail($id);

            $validated = $request->validate([
                'street' => 'nullable|string|max:255',
                'purok' => 'nullable|string|max:100',
                'contact_number' => 'nullable|string|max:50',
                'emergency_contact' => 'nullable|string|max:50',
            ]);

            $household->address->update([
                'street' => $validated['street'] ?? $household->address->street,
                'purok' => $validated['purok'] ?? $household->address->purok,
            ]);

            $household->update([
                'contact_number' => $validated['contact_number'] ?? $household->contact_number,
                'emergency_contact' => $validated['emergency_contact'] ?? $household->emergency_contact,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Household updated successfully',
                'data' => $household->fresh()->load(['address.barangay.city.province.region', 'members']),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Household not found',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('API Household update error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update household',
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $household = Household::findOrFail($id);
            $household->members()->delete();
            $household->address()->delete();
            User::where('household_id', $household->id)->delete();
            $household->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Household deleted successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Household not found',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('API Household destroy error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete household',
            ], 500);
        }
    }

    public function uploadCsv(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            ]);

            $file = $request->file('csv_file');
            
            if (!$file->isValid() || !$file->isReadable()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Uploaded file is not valid or readable',
                ], 400);
            }

            $tempFilePath = $file->getRealPath();
            \Log::info("API: Processing CSV from temp path: {$tempFilePath}");

            $service = new HouseholdCsvImportService();
            $result = $service->import($tempFilePath, $request->user()->id);

            return response()->json([
                'status' => 'success',
                'message' => $result['message'],
                'stats' => $result['stats'],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('API CSV upload error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function generateUniqueEmail(string $firstName, string $lastName): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '_', trim($firstName . '_' . $lastName)));
        $slug = substr($slug, 0, 40);
        $email = $slug . '@household.local';
        $attempt = 1;

        while (User::where('email', $email)->exists()) {
            $email = $slug . '_' . $attempt . '@household.local';
            $attempt++;
        }

        return $email;
    }
}
