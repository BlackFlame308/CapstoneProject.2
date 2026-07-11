<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Household;
use App\Models\Member;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * HouseholdService
 *
 * Handles household creation, update, and deletion including
 * all related records (address, user account, members).
 */
class HouseholdService
{
    /**
     * Create a household with address, user account, and members.
     * The household head is also added as a member.
     */
    public function create(array $data, string $createdBy): Household
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $role    = Role::where('name', 'Household')->firstOrFail();
            $address = Address::create($this->buildAddressData($data));

            $householdCode = Household::generateHouseholdId();
            $allMembers    = $this->collectMembers($data);

            $household = Household::create([
                'household_id'      => $householdCode,
                'household_code'    => $householdCode,
                'household_name'    => $data['household_name'] ?? 'Unnamed Household',
                'email'             => $data['email'] ?? null,
                'member_count'      => count($allMembers),
                'address_id'        => $address->address_id,
                'contact_number'    => $data['contact_number'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'created_by'        => $createdBy,
            ]);

            $this->createHouseholdUser($household, $data, $role);

            foreach ($allMembers as $memberData) {
                Member::create(MemberDataBuilder::build($memberData, $household->household_id));
            }

            return $household;
        });
    }

    /**
     * Update a household and its address and members.
     */
    public function update(Household $household, array $data): Household
    {
        return DB::transaction(function () use ($household, $data) {
            $memberIds = [];

            if (array_key_exists('members', $data)) {
                $memberIds = $this->syncMembers($household, $data['members'] ?? []);
            }

            $memberCount = array_key_exists('members', $data)
                ? count($memberIds)
                : $household->members()->count();

            $household->update([
                'household_name'    => $data['household_name'] ?? $household->household_name,
                'email'             => $data['email'] ?? $household->email,
                'member_count'      => $memberCount,
                'contact_number'    => $data['contact_number'] ?? $household->contact_number,
                'emergency_contact' => $data['emergency_contact'] ?? $household->emergency_contact,
            ]);

            if ($household->address) {
                $household->address->update($this->buildAddressUpdate($data, $household->address));
            }

            return $household->fresh(['address', 'members']);
        });
    }

    /**
     * Delete a household and all related records.
     */
    public function delete(Household $household): void
    {
        DB::transaction(function () use ($household) {
            $household->members()->delete();
            User::where('household_id', $household->household_id)->delete();
            $household->address?->delete();
            $household->delete();
        });
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function buildAddressData(array $data): array
    {
        return [
            'street'        => $data['street']        ?? null,
            'purok_sitio'   => $data['purok_sitio']   ?? null,
            'house_number'  => $data['house_number']  ?? null,
            'zip_code'      => $data['zip_code']      ?? null,
            'full_address'  => $data['full_address']  ?? null,
            'barangay_id'   => $data['barangay_id']   ?? null,
            'barangay_name' => $data['barangay_name'] ?? null,
        ];
    }

    private function buildAddressUpdate(array $data, $address): array
    {
        $pick = fn($key) => array_key_exists($key, $data) ? $data[$key] : $address->$key;

        return [
            'street'        => $pick('street'),
            'purok_sitio'   => $pick('purok_sitio'),
            'house_number'  => $pick('house_number'),
            'zip_code'      => $pick('zip_code'),
            'full_address'  => $pick('full_address'),
            'barangay_id'   => $pick('barangay_id'),
            'barangay_name' => $pick('barangay_name'),
        ];
    }

    private function collectMembers(array $data): array
    {
        $members = [];

        if (!empty($data['head_first_name']) && !empty($data['head_last_name'])) {
            $members[] = [
                'first_name'      => $data['head_first_name'],
                'middle_name'     => $data['head_middle_name'] ?? null,
                'last_name'       => $data['head_last_name'],
                'birth_date'      => $data['head_birth_date'] ?? null,
                'sex'             => $data['head_sex'] ?? 'M',
                'relation'        => 'Head',
                'civil_status'    => $data['head_civil_status'] ?? null,
                'education_level' => $data['head_education_level'] ?? null,
                'occupation'      => $data['head_occupation'] ?? null,
                'is_pwd'          => $data['head_is_pwd'] ?? false,
                'is_pregnant'     => $data['head_is_pregnant'] ?? false,
            ];
        }

        foreach ($data['members'] ?? [] as $m) {
            if (!empty($m['first_name']) && !empty($m['last_name'])) {
                $members[] = $m;
            }
        }

        return $members;
    }

    private function createHouseholdUser(Household $household, array $data, $role): void
    {
        $tempPassword = Str::random(10);
        $headName     = trim(
            $data['head_first_name'] . ' ' .
            (!empty($data['head_middle_name']) ? $data['head_middle_name'] . ' ' : '') .
            $data['head_last_name']
        );
        $userEmail = !empty($data['email'])
            ? $data['email']
            : strtolower("{$household->household_code}@households.capstone.local");

        User::create([
            'name'                 => $headName,
            'email'                => $userEmail,
            'password'             => bcrypt($tempPassword),
            'role_id'              => $role->role_id,
            'household_id'         => $household->household_id,
            'must_change_password' => true,
            'temp_password'        => $tempPassword,
        ]);
    }

    private function syncMembers(Household $household, array $members): array
    {
        $keptIds = [];

        foreach ($members as $memberData) {
            if (empty($memberData['first_name']) || empty($memberData['last_name'])) continue;

            $built = MemberDataBuilder::build($memberData, $household->household_id);

            if (!empty($memberData['member_id'])) {
                $member = Member::find($memberData['member_id']);
                if ($member && $member->household_id === $household->household_id) {
                    $member->update($built);
                    $keptIds[] = $member->member_id;
                }
            } else {
                $keptIds[] = Member::create($built)->member_id;
            }
        }

        $toDelete = array_diff(
            $household->members()->pluck('member_id')->toArray(),
            $keptIds
        );
        if (!empty($toDelete)) {
            Member::whereIn('member_id', $toDelete)->delete();
        }

        return $keptIds;
    }
}
