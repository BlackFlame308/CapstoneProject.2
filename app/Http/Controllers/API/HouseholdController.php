<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Household;
use App\Models\Member;
use App\Models\Address;
use App\Models\Role;
use App\Models\User;
use App\Services\HouseholdCsvImportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HouseholdController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Household::with([
                'address.barangay.city.province.region',
                'members',
            ]);

            if ($request->filled('purok_sitio')) {
                $query->whereHas('address', function ($q) use ($request) {
                    $q->where('purok_sitio', 'like', '%' . $request->purok_sitio . '%');
                });
            }

            if ($request->filled('barangay_id')) {
                $query->whereHas('address', function ($q) use ($request) {
                    $q->where('barangay_id', $request->barangay_id);
                });
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('household_code', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQ) use ($search) {
                          $userQ->where('name', 'like', "%{$search}%");
                      });
                });
            }

            $households = $query->latest()->paginate(15);

            return response()->json([
                'status' => 'success',
                'data'   => $households,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('API Household index error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to load households',
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'household_name'              => 'required|string|max:100',
                'email'                       => 'nullable|email|max:150|unique:households,email|unique:users,email',
                'street'                      => 'nullable|string|max:255',
                'purok_sitio'                 => 'nullable|string|max:150',
                'house_number'                => 'nullable|string|max:100',
                'zip_code'                    => 'nullable|string|max:20',
                'full_address'                => 'nullable|string|max:500',
                'barangay_id'                 => 'nullable|exists:barangays,id',
                'barangay_name'               => 'nullable|string|max:100|required_without:barangay_id',
                'contact_number'              => 'nullable|string|max:50',
                'emergency_contact'           => 'nullable|string|max:50',
                'head_first_name'             => 'required|string|max:100',
                'head_middle_name'            => 'nullable|string|max:100',
                'head_last_name'              => 'required|string|max:100',
                'members'                     => 'nullable|array',
                'members.*.first_name'        => 'required_with:members|string|max:100',
                'members.*.middle_name'       => 'nullable|string|max:100',
                'members.*.last_name'         => 'required_with:members|string|max:100',
                'members.*.birth_date'        => 'required_with:members|date',
                'members.*.sex'               => 'required_with:members|in:M,F',
                'members.*.relation'          => 'nullable|string|max:50',
                'members.*.civil_status'      => 'nullable|string|max:50',
                'members.*.education_level'   => 'nullable|string|max:100',
                'members.*.occupation'        => 'nullable|string|max:100',
                'members.*.is_pwd'            => 'nullable|boolean',
                'members.*.is_pregnant'       => 'nullable|boolean',
            ]);

            $householdRole = Role::where('name', 'Household')->first();
            if (!$householdRole) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Household role not configured. Run seeders.',
                ], 500);
            }

            $household = DB::transaction(function () use ($validated, $request, $householdRole) {
                $address = Address::create([
                    'street'        => $validated['street'] ?? null,
                    'purok_sitio'   => $validated['purok_sitio'] ?? null,
                    'house_number'  => $validated['house_number'] ?? null,
                    'zip_code'      => $validated['zip_code'] ?? null,
                    'full_address'  => $validated['full_address'] ?? null,
                    'barangay_id'   => $validated['barangay_id'] ?? null,
                    'barangay_name' => $validated['barangay_name'] ?? null,
                ]);

                $householdCode = Household::generateHouseholdId();

                $household = Household::create([
                    'id'                => $householdCode,
                    'household_code'    => $householdCode,
                    'household_name'    => $validated['household_name'],
                    'email'             => $validated['email'] ?? null,
                    'member_count'      => count($validated['members'] ?? []),
                    'address_id'        => $address->id,
                    'contact_number'    => $validated['contact_number']    ?? null,
                    'emergency_contact' => $validated['emergency_contact'] ?? null,
                    'created_by'        => $request->user()->id,
                ]);

                $userEmail    = !empty($validated['email']) ? $validated['email'] : strtolower("{$householdCode}@household.local");
                $tempPassword = 'Temp_' . Str::random(8);
                $headName     = trim(
                    $validated['head_first_name'] . ' ' .
                    ($validated['head_middle_name'] ? $validated['head_middle_name'] . ' ' : '') .
                    $validated['head_last_name']
                );

                $household->user()->create([
                    'name'                 => $headName,
                    'email'                => $userEmail,
                    'password'             => bcrypt($tempPassword),
                    'role_id'              => $householdRole->role_id,
                    'household_id'         => $household->id,
                    'must_change_password' => true,
                    'temp_password'        => $tempPassword,
                ]);

                foreach ($validated['members'] ?? [] as $memberData) {
                    $age  = (int) Carbon::parse($memberData['birth_date'])->diffInYears(now());
                    $sex  = $memberData['sex'];
                    $gender = $sex === 'M' ? 'male' : 'female';
                    $isPwd = $memberData['is_pwd'] ?? false;

                    $specialNeeds = $isPwd ? 'pwd'
                        : ($age >= 60 ? 'senior'
                            : ($age < 18 ? 'child' : 'adult'));

                    $fullName = trim(
                        $memberData['first_name'] . ' ' .
                        (!empty($memberData['middle_name']) ? $memberData['middle_name'] . ' ' : '') .
                        $memberData['last_name']
                    );

                    Member::create([
                        'household_id'    => $household->id,
                        'name'            => $fullName,
                        'gender'          => $gender,
                        'sex'             => $sex,
                        'age'             => $age,
                        'special_needs'   => $specialNeeds,
                        'first_name'      => $memberData['first_name'],
                        'middle_name'     => $memberData['middle_name'] ?? null,
                        'last_name'       => $memberData['last_name'],
                        'birth_date'      => $memberData['birth_date'],
                        'civil_status'    => $memberData['civil_status']    ?? null,
                        'education_level' => $memberData['education_level'] ?? null,
                        'occupation'      => $memberData['occupation']      ?? null,
                        'relation'        => $memberData['relation']        ?? null,
                        'is_pwd'          => $isPwd,
                        'is_pregnant'     => $memberData['is_pregnant']     ?? false,
                        'is_graduate'     => false,
                    ]);
                }

                return $household;
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Household created successfully',
                'data'    => $household->load([
                    'address.barangay.city.province.region',
                    'members',
                ]),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('API Household store error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to create household',
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $household = Household::with([
                'address.barangay.city.province.region',
                'members',
            ])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data'   => $household,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'not_found',
                'message' => 'Household not found',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('API Household show error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to load household',
            ], 500);
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
                if ($household->address) {
                    $household->address->update([
                        'street'       => $validated['street'] ?? $household->address->street,
                        'purok_sitio'  => $validated['purok_sitio'] ?? $household->address->purok_sitio,
                        'full_address' => $validated['full_address'] ?? $household->address->full_address,
                    ]);
                }

                $household->update([
                    'household_name'    => $validated['household_name'] ?? $household->household_name,
                    'email'             => $validated['email'] ?? $household->email,
                    'contact_number'    => $validated['contact_number']    ?? $household->contact_number,
                    'emergency_contact' => $validated['emergency_contact'] ?? $household->emergency_contact,
                ]);
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Household updated successfully',
                'data'    => $household->fresh()->load([
                    'address.barangay.city.province.region',
                    'members',
                ]),
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'not_found',
                'message' => 'Household not found',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('API Household update error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update household',
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $household = Household::with('address')->findOrFail($id);

            DB::transaction(function () use ($household) {
                $household->members()->delete();
                User::where('household_id', $household->id)->delete();
                if ($household->address) {
                    $household->address->delete();
                }
                $household->delete();
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Household deleted successfully',
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'not_found',
                'message' => 'Household not found',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('API Household destroy error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to delete household',
            ], 500);
        }
    }

    public function uploadCsv(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            ]);

            $tempFilePath = $request->file('csv_file')->getRealPath();
            \Log::info("API: Processing CSV from temp path: {$tempFilePath}");

            $service = new HouseholdCsvImportService();
            $result  = $service->import($tempFilePath, $request->user()->id);

            return response()->json([
                'status'  => 'success',
                'message' => $result['message'],
                'stats'   => $result['stats'],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('API CSV upload error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'CSV import failed. Please check the file format and try again.',
            ], 500);
        }
    }

}
