<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MemberController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $members = Member::with('household.address.barangay.city.province.region')->paginate(15);

            return response()->json([
                'status' => 'success',
                'data' => $members,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('API Member index error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load members',
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'household_id' => 'required|exists:households,id',
                'first_name' => 'required|string|max:100',
                'middle_name' => 'nullable|string|max:100',
                'last_name' => 'required|string|max:100',
                'birth_date' => 'required|date',
                'sex' => 'required|in:M,F',
                'civil_status' => 'nullable|string|max:50',
                'education_level' => 'nullable|string|max:100',
                'profession' => 'nullable|string|max:100',
                'is_pwd' => 'nullable|boolean',
            ]);

            $member = Member::create([
                'household_id' => $validated['household_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'birth_date' => $validated['birth_date'],
                'sex' => $validated['sex'],
                'civil_status' => $validated['civil_status'] ?? null,
                'education_level' => $validated['education_level'] ?? null,
                'profession' => $validated['profession'] ?? null,
                'is_pwd' => $validated['is_pwd'] ?? false,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Member created successfully',
                'data' => $member,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('API Member store error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create member',
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $member = Member::with('household.address.barangay.city.province.region')->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $member,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Member not found',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('API Member show error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load member',
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $member = Member::findOrFail($id);

            $validated = $request->validate([
                'first_name' => 'nullable|string|max:100',
                'middle_name' => 'nullable|string|max:100',
                'last_name' => 'nullable|string|max:100',
                'birth_date' => 'nullable|date',
                'sex' => 'nullable|in:M,F',
                'civil_status' => 'nullable|string|max:50',
                'education_level' => 'nullable|string|max:100',
                'profession' => 'nullable|string|max:100',
                'is_pwd' => 'nullable|boolean',
            ]);

            $member->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Member updated successfully',
                'data' => $member,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'not_found',
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
                'status' => 'error',
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
                'status' => 'success',
                'message' => 'Member deleted successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Member not found',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('API Member destroy error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete member',
            ], 500);
        }
    }
}
