<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MemberController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $members = Member::with(
                'household.address.barangay.city.province.region'
            )->paginate(15);

            return response()->json([
                'status' => 'success',
                'data'   => $members,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('API Member index error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to load members',
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'household_id'   => 'required|exists:households,id',
                'first_name'     => 'required|string|max:100',
                'middle_name'    => 'nullable|string|max:100',
                'last_name'      => 'required|string|max:100',
                'birth_date'     => 'required|date',
                'sex'            => 'required|in:M,F',
                'civil_status'   => 'nullable|string|max:50',
                'education_level'=> 'nullable|string|max:100',
                'profession'     => 'nullable|string|max:100',
                'is_pwd'         => 'nullable|boolean',
            ]);

            $isPwd = $validated['is_pwd'] ?? false;
            $age   = (int) Carbon::parse($validated['birth_date'])->diffInYears(now());
            $sex   = $validated['sex'] === 'M' ? 'male' : 'female';

            $specialNeeds = $isPwd ? 'pwd'
                : ($age >= 60 ? 'senior' : ($age < 18 ? 'child' : 'adult'));

            $fullName = trim(
                $validated['first_name'] . ' ' .
                (!empty($validated['middle_name']) ? $validated['middle_name'] . ' ' : '') .
                $validated['last_name']
            );

            $member = Member::create([
                'household_id'    => $validated['household_id'],
                // Computed / legacy columns
                'name'            => $fullName,
                'gender'          => $sex,
                'age'             => $age,
                'special_needs'   => $specialNeeds,
                // Detailed columns
                'first_name'      => $validated['first_name'],
                'middle_name'     => $validated['middle_name']     ?? null,
                'last_name'       => $validated['last_name'],
                'birth_date'      => $validated['birth_date'],
                'sex'             => $sex,
                'civil_status'    => $validated['civil_status']    ?? null,
                'education_level' => $validated['education_level'] ?? null,
                'profession'      => $validated['profession']       ?? null,
                'is_pwd'          => $isPwd,
                'is_graduate'     => false,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Member created successfully',
                'data'    => $member->load('household.address.barangay.city.province.region'),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('API Member store error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to create member',
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $member = Member::with(
                'household.address.barangay.city.province.region'
            )->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data'   => $member,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'not_found',
                'message' => 'Member not found',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('API Member show error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to load member',
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $member = Member::findOrFail($id);

            $validated = $request->validate([
                'first_name'     => 'sometimes|required|string|max:100',
                'middle_name'    => 'nullable|string|max:100',
                'last_name'      => 'sometimes|required|string|max:100',
                'birth_date'     => 'sometimes|required|date',
                'sex'            => 'sometimes|required|in:M,F',
                'civil_status'   => 'nullable|string|max:50',
                'education_level'=> 'nullable|string|max:100',
                'profession'     => 'nullable|string|max:100',
                'is_pwd'         => 'nullable|boolean',
            ]);

            // Only update fields that were actually provided
            $updateData = collect($validated)
                ->reject(fn($v) => is_null($v))
                ->toArray();

            // Recompute derived fields if relevant inputs changed
            $needsRecompute = isset($updateData['birth_date'])
                || isset($updateData['sex'])
                || isset($updateData['is_pwd']);

            if ($needsRecompute) {
                $birthDate = $updateData['birth_date'] ?? $member->birth_date;
                $sexRaw    = $updateData['sex']        ?? ($member->sex === 'male' ? 'M' : 'F');
                $isPwd     = $updateData['is_pwd']     ?? $member->is_pwd;

                $age          = (int) Carbon::parse($birthDate)->diffInYears(now());
                $sex          = $sexRaw === 'M' ? 'male' : 'female';
                $specialNeeds = $isPwd ? 'pwd'
                    : ($age >= 60 ? 'senior' : ($age < 18 ? 'child' : 'adult'));

                $updateData['age']           = $age;
                $updateData['sex']           = $sex;
                $updateData['gender']        = $sex;
                $updateData['special_needs'] = $specialNeeds;
            }

            // Recompute full name if any name part changed
            $nameChanged = isset($updateData['first_name'])
                || isset($updateData['middle_name'])
                || isset($updateData['last_name']);

            if ($nameChanged) {
                $middle = $updateData['middle_name'] ?? $member->middle_name;
                $updateData['name'] = trim(
                    ($updateData['first_name'] ?? $member->first_name) . ' ' .
                    ($middle ? $middle . ' ' : '') .
                    ($updateData['last_name'] ?? $member->last_name)
                );
            }

            $member->update($updateData);

            return response()->json([
                'status'  => 'success',
                'message' => 'Member updated successfully',
                'data'    => $member->fresh()->load(
                    'household.address.barangay.city.province.region'
                ),
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'not_found',
                'message' => 'Member not found',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('API Member update error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update member',
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $member = Member::findOrFail($id);
            $member->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Member deleted successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'not_found',
                'message' => 'Member not found',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('API Member destroy error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to delete member',
            ], 500);
        }
    }
}