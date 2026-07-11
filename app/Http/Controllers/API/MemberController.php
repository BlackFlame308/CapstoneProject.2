<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\MemberDataBuilder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * MemberController (API)
 *
 * CRUD operations for household members via the REST API.
 */
class MemberController extends Controller
{
    private const WITH_PATH = 'household.address.barangay.city.province.region';

    public function index(): JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success',
                'data'   => Member::with(self::WITH_PATH)->paginate(15),
            ]);
        } catch (\Exception $e) {
            \Log::error('API Member index error: ' . $e->getMessage());
            return $this->serverError('Failed to load members');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'household_id'    => 'required|exists:households,household_id',
                'first_name'      => 'required|string|max:100',
                'middle_name'     => 'nullable|string|max:100',
                'last_name'       => 'required|string|max:100',
                'birth_date'      => 'required|date',
                'sex'             => 'required|in:M,F',
                'civil_status'    => 'nullable|string|max:50',
                'education_level' => 'nullable|string|max:100',
                'profession'      => 'nullable|string|max:100',
                'is_pwd'          => 'nullable|boolean',
            ]);

            $member = Member::create(MemberDataBuilder::build($validated, $validated['household_id']));

            return response()->json([
                'status'  => 'success',
                'message' => 'Member created successfully',
                'data'    => $member->load(self::WITH_PATH),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'validation_error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('API Member store error: ' . $e->getMessage());
            return $this->serverError('Failed to create member');
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $member = Member::with(self::WITH_PATH)->findOrFail($id);
            return response()->json(['status' => 'success', 'data' => $member]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Member not found');
        } catch (\Exception $e) {
            \Log::error('API Member show error: ' . $e->getMessage());
            return $this->serverError('Failed to load member');
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $member    = Member::findOrFail($id);
            $validated = $request->validate([
                'first_name'      => 'sometimes|required|string|max:100',
                'middle_name'     => 'nullable|string|max:100',
                'last_name'       => 'sometimes|required|string|max:100',
                'birth_date'      => 'sometimes|required|date',
                'sex'             => 'sometimes|required|in:M,F',
                'civil_status'    => 'nullable|string|max:50',
                'education_level' => 'nullable|string|max:100',
                'profession'      => 'nullable|string|max:100',
                'is_pwd'          => 'nullable|boolean',
            ]);

            $updateData = $this->buildUpdateData($member, $validated);
            $member->update($updateData);

            return response()->json([
                'status'  => 'success',
                'message' => 'Member updated successfully',
                'data'    => $member->fresh()->load(self::WITH_PATH),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Member not found');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'validation_error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('API Member update error: ' . $e->getMessage());
            return $this->serverError('Failed to update member');
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            Member::findOrFail($id)->delete();
            return response()->json(['status' => 'success', 'message' => 'Member deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Member not found');
        } catch (\Exception $e) {
            \Log::error('API Member destroy error: ' . $e->getMessage());
            return $this->serverError('Failed to delete member');
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function buildUpdateData(Member $member, array $validated): array
    {
        $updateData = collect($validated)->reject(fn($v) => is_null($v))->toArray();

        if (isset($updateData['birth_date']) || isset($updateData['sex']) || isset($updateData['is_pwd'])) {
            $birthDate = $updateData['birth_date'] ?? $member->birth_date;
            $sexRaw    = $updateData['sex']        ?? ($member->sex === 'male' ? 'M' : 'F');
            $isPwd     = $updateData['is_pwd']     ?? $member->is_pwd;

            $age    = (int) Carbon::parse($birthDate)->diffInYears(now());
            $sex    = MemberDataBuilder::normalizeSex($sexRaw);
            $gender = $sex === 'M' ? 'male' : 'female';

            $updateData['age']           = $age;
            $updateData['sex']           = $gender;
            $updateData['gender']        = $gender;
            $updateData['special_needs'] = MemberDataBuilder::build(
                ['is_pwd' => $isPwd, 'first_name' => '', 'last_name' => '', 'birth_date' => $birthDate, 'sex' => $sex],
                $member->household_id
            )['special_needs'];
        }

        if (isset($updateData['first_name']) || isset($updateData['middle_name']) || isset($updateData['last_name'])) {
            $updateData['name'] = MemberDataBuilder::buildFullName([
                'first_name'  => $updateData['first_name']  ?? $member->first_name,
                'middle_name' => $updateData['middle_name'] ?? $member->middle_name,
                'last_name'   => $updateData['last_name']   ?? $member->last_name,
            ]);
        }

        return $updateData;
    }

    private function notFound(string $message): JsonResponse
    {
        return response()->json(['status' => 'not_found', 'message' => $message], 404);
    }

    private function serverError(string $message): JsonResponse
    {
        return response()->json(['status' => 'error', 'message' => $message], 500);
    }
}