<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Household;
use App\Models\Member;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HouseholdService
{
    /**
     * Create a household with address, user account, and members.
     */
    public function create(array $data, string $createdBy): Household
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $role = Role::where('name', 'Household')->firstOrFail();

            $address = Address::create([
                'street'        => $data['street'] ?? null,
                'purok_sitio'   => $data['purok_sitio'] ?? null,
                'house_number'  => $data['house_number'] ?? null,
                'zip_code'      => $data['zip_code'] ?? null,
                'full_address'  => $data['full_address'] ?? null,
                'barangay_id'   => $data['barangay_id'] ?? null,
                'barangay_name' => $data['barangay_name'] ?? null,
            ]);

            $householdCode = 'HH-' . strtoupper(Str::random(8));
            $household = Household::create([
                'household_code'    => $householdCode,
                'household_name'    => $data['household_name'] ?? 'Unnamed Household',
                'email'             => $data['email'] ?? null,
                'member_count'      => count($data['members'] ?? []),
                'address_id'        => $address->id,
                'contact_number'    => $data['contact_number'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'created_by'        => $createdBy,
            ]);

            $tempPassword = Str::password(12);
            $headName = trim(
                $data['head_first_name'] . ' ' .
                (!empty($data['head_middle_name']) ? $data['head_middle_name'] . ' ' : '') .
                $data['head_last_name']
            );

            $userEmail = !empty($data['email']) ? $data['email'] : strtolower("{$householdCode}@households.capstone.local");

            User::create([
                'name'                 => $headName,
                'email'                => $userEmail,
                'password'             => bcrypt($tempPassword),
                'role_id'              => $role->id,
                'household_id'         => $household->id,
                'must_change_password' => true,
                'temp_password'        => $tempPassword,
            ]);

            foreach ($data['members'] ?? [] as $memberData) {
                if (!empty($memberData['first_name']) && !empty($memberData['last_name']) && !empty($memberData['birth_date'])) {
                    Member::create($this->buildMemberData($memberData, $household->id));
                }
            }

            return $household;
        });
    }

    /**
     * Update a household and its address.
     */
    public function update(Household $household, array $data): Household
    {
        return DB::transaction(function () use ($household, $data) {
            $household->update([
                'household_name'    => $data['household_name'] ?? $household->household_name,
                'email'             => $data['email'] ?? $household->email,
                'member_count'      => isset($data['members']) ? count($data['members']) : $household->member_count,
                'contact_number'    => $data['contact_number'] ?? $household->contact_number,
                'emergency_contact' => $data['emergency_contact'] ?? $household->emergency_contact,
            ]);

            if ($household->address) {
                $household->address->update([
                    'street'        => $data['street'] ?? $household->address->street,
                    'purok_sitio'   => $data['purok_sitio'] ?? $household->address->purok_sitio,
                    'house_number'  => array_key_exists('house_number', $data) ? $data['house_number'] : $household->address->house_number,
                    'zip_code'      => array_key_exists('zip_code', $data) ? $data['zip_code'] : $household->address->zip_code,
                    'full_address'  => array_key_exists('full_address', $data) ? $data['full_address'] : $household->address->full_address,
                    'barangay_id'   => array_key_exists('barangay_id', $data) ? $data['barangay_id'] : $household->address->barangay_id,
                    'barangay_name' => array_key_exists('barangay_name', $data) ? $data['barangay_name'] : $household->address->barangay_name,
                ]);
            }

            foreach ($data['members'] ?? [] as $memberData) {
                if (!empty($memberData['id'])) {
                    $member = Member::find($memberData['id']);
                    if ($member && $member->household_id === $household->id) {
                        $member->update($this->buildMemberData($memberData, $household->id));
                    }
                } elseif (!empty($memberData['first_name']) && !empty($memberData['last_name']) && !empty($memberData['birth_date'])) {
                    Member::create($this->buildMemberData($memberData, $household->id));
                }
            }

            return $household->fresh();
        });
    }

    /**
     * Delete a household and all related records.
     */
    public function delete(Household $household): void
    {
        DB::transaction(function () use ($household) {
            $household->members()->delete();
            User::where('household_id', $household->id)->delete();
            if ($household->address) {
                $household->address->delete();
            }
            $household->delete();
        });
    }

    /**
     * Build normalized member data array.
     */
    private function buildMemberData(array $data, string $householdId): array
    {
        $age   = (int) Carbon::parse($data['birth_date'])->diffInYears(now());
        $sex   = ($data['sex'] ?? 'M') === 'M' ? 'male' : 'female';
        $isPwd = $data['is_pwd'] ?? false;

        return [
            'household_id'    => $householdId,
            'name'            => trim(
                $data['first_name'] . ' ' .
                (!empty($data['middle_name']) ? $data['middle_name'] . ' ' : '') .
                $data['last_name']
            ),
            'gender'          => $sex,
            'sex'             => $sex,
            'age'             => $age,
            'special_needs'   => $isPwd ? 'pwd' : ($age >= 60 ? 'senior' : ($age < 18 ? 'child' : 'adult')),
            'first_name'      => $data['first_name'],
            'middle_name'     => $data['middle_name'] ?? null,
            'last_name'       => $data['last_name'],
            'birth_date'      => $data['birth_date'],
            'civil_status'    => $data['civil_status']    ?? null,
            'education_level' => $data['education_level'] ?? null,
            'occupation'      => $data['occupation']      ?? null,
            'relation'        => $data['relation']        ?? null,
            'is_pwd'          => $isPwd,
            'is_pregnant'     => $data['is_pregnant']     ?? false,
            'is_graduate'     => false,
        ];
    }
}
