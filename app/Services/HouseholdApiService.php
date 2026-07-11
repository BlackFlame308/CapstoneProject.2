<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Household;
use App\Models\Member;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * HouseholdApiService
 *
 * Dedicated service for handling API-specific logic for households.
 */
class HouseholdApiService
{
    /**
     * Apply search and location filters to the household query.
     */
    public function applyIndexFilters($query, Request $request): void
    {
        if ($request->filled('purok_sitio')) {
            $query->whereHas('address', fn($q) => $q->where('purok_sitio', 'like', '%' . $request->purok_sitio . '%'));
        }
        if ($request->filled('barangay_id')) {
            $query->whereHas('address', fn($q) => $q->where('barangay_id', $request->barangay_id));
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q->where('household_code', 'like', "%{$search}%")
                ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%")));
        }
    }

    /**
     * Create a household along with its address, user account, and members.
     */
    public function createHouseholdWithMembers(array $validated, $user, Role $role): Household
    {
        $address = Address::create([
            'street'        => $validated['street']        ?? null,
            'purok_sitio'   => $validated['purok_sitio']   ?? null,
            'house_number'  => $validated['house_number']  ?? null,
            'zip_code'      => $validated['zip_code']      ?? null,
            'full_address'  => $validated['full_address']  ?? null,
            'barangay_id'   => $validated['barangay_id']   ?? null,
            'barangay_name' => $validated['barangay_name'] ?? null,
        ]);

        $code      = Household::generateHouseholdId();
        $household = Household::create([
            'household_code'    => $code,
            'household_name'    => $validated['household_name'],
            'email'             => $validated['email']             ?? null,
            'member_count'      => count($validated['members']     ?? []),
            'address_id'        => $address->address_id,
            'contact_number'    => $validated['contact_number']    ?? null,
            'emergency_contact' => $validated['emergency_contact'] ?? null,
            'created_by'        => $user->user_id,
        ]);

        $tempPassword = 'Temp_' . Str::random(8);
        $headName     = MemberDataBuilder::buildFullName([
            'first_name'  => $validated['head_first_name'],
            'middle_name' => $validated['head_middle_name'] ?? null,
            'last_name'   => $validated['head_last_name'],
        ]);
        $userEmail = !empty($validated['email'])
            ? $validated['email']
            : strtolower("{$code}@household.local");

        $household->user()->create([
            'name'                 => $headName,
            'email'                => $userEmail,
            'password'             => bcrypt($tempPassword),
            'role_id'              => $role->role_id,
            'household_id'         => $household->household_id,
            'must_change_password' => true,
            'temp_password'        => $tempPassword,
        ]);

        foreach ($validated['members'] ?? [] as $memberData) {
            Member::create(MemberDataBuilder::build($memberData, $household->household_id));
        }

        return $household;
    }
}
