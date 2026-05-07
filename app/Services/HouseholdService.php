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
     * The household head is also added as a member.
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

            $householdCode = Household::generateHouseholdId();
            
            // Collect all members including the head
            $allMembers = [];
            
            // Add household head as first member (if provided)
            if (!empty($data['head_first_name']) && !empty($data['head_last_name'])) {
                $allMembers[] = [
                    'first_name'   => $data['head_first_name'],
                    'middle_name'  => $data['head_middle_name'] ?? null,
                    'last_name'    => $data['head_last_name'],
                    'birth_date'  => $data['head_birth_date'] ?? null,
                    'sex'         => $data['head_sex'] ?? 'M',
                    'relation'    => 'Head',
                    'civil_status' => $data['head_civil_status'] ?? null,
                    'education_level' => $data['head_education_level'] ?? null,
                    'occupation'  => $data['head_occupation'] ?? null,
                    'is_pwd'      => $data['head_is_pwd'] ?? false,
                    'is_pregnant' => $data['head_is_pregnant'] ?? false,
                ];
            }
            
            // Add additional members from the members array
            foreach ($data['members'] ?? [] as $memberData) {
                if (!empty($memberData['first_name']) && !empty($memberData['last_name'])) {
                    $allMembers[] = $memberData;
                }
            }
            
            $memberCount = count($allMembers);

            $household = Household::create([
                'id'                => $householdCode,
                'household_code'    => $householdCode,
                'household_name'    => $data['household_name'] ?? 'Unnamed Household',
                'email'             => $data['email'] ?? null,
                'member_count'      => $memberCount,
                'address_id'        => $address->id,
                'contact_number'    => $data['contact_number'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'created_by'        => $createdBy,
            ]);

            // Create user account for household head
            $tempPassword = Str::random(10);
            $headName = trim(
                $data['head_first_name'] . ' ' .
                (!empty($data['head_middle_name']) ? $data['head_middle_name'] . ' ' : '') .
                $data['head_last_name']
            );

            $userEmail = !empty($data['email'])
                ? $data['email']
                : strtolower("{$householdCode}@households.capstone.local");

            User::create([
                'name'                 => $headName,
                'email'                => $userEmail,
                'password'             => bcrypt($tempPassword),
                'role_id'              => $role->id,
                'household_id'         => $household->id,
                'must_change_password' => true,
                'temp_password'        => $tempPassword,
            ]);

            // Create member records for all members including the head
            foreach ($allMembers as $memberData) {
                Member::create($this->buildMemberData($memberData, $household->id));
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
            // Process members (existing + new)
            $allMembers = [];
            
            // Process existing members (keep ones with id)
            if (array_key_exists('members', $data)) {
                foreach ($data['members'] ?? [] as $memberData) {
                    if (empty($memberData['first_name']) || empty($memberData['last_name'])) {
                        continue;
                    }

                    if (!empty($memberData['id'])) {
                        $member = Member::find($memberData['id']);
                        if ($member && $member->household_id === $household->id) {
                            $member->update($this->buildMemberData($memberData, $household->id));
                            $allMembers[] = $member->id;
                        }
                    } else {
                        $newMember = Member::create($this->buildMemberData($memberData, $household->id));
                        $allMembers[] = $newMember->id;
                    }
                }

                $currentMemberIds = $household->members()->pluck('members.id')->toArray();
                $membersToDelete = array_diff($currentMemberIds, $allMembers);
                if (!empty($membersToDelete)) {
                    Member::whereIn('id', $membersToDelete)->delete();
                }
            }

            $memberCount = array_key_exists('members', $data)
                ? count($allMembers)
                : $household->members()->count();

            $household->update([
                'household_name'    => $data['household_name'] ?? $household->household_name,
                'email'             => $data['email'] ?? $household->email,
                'member_count'      => $memberCount,
                'contact_number'    => $data['contact_number'] ?? $household->contact_number,
                'emergency_contact' => $data['emergency_contact'] ?? $household->emergency_contact,
            ]);

            if ($household->address) {
                $household->address->update([
                    'street'        => $data['street']       ?? $household->address->street,
                    'purok_sitio'   => $data['purok_sitio']  ?? $household->address->purok_sitio,
                    'house_number'  => array_key_exists('house_number', $data)  ? $data['house_number']  : $household->address->house_number,
                    'zip_code'      => array_key_exists('zip_code', $data)      ? $data['zip_code']      : $household->address->zip_code,
                    'full_address'  => array_key_exists('full_address', $data)  ? $data['full_address']  : $household->address->full_address,
                    'barangay_id'   => array_key_exists('barangay_id', $data)   ? $data['barangay_id']   : $household->address->barangay_id,
                    'barangay_name' => array_key_exists('barangay_name', $data) ? $data['barangay_name'] : $household->address->barangay_name,
                ]);
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
            User::where('household_id', $household->id)->delete();
            if ($household->address) {
                $household->address->delete();
            }
            $household->delete();
        });
    }

    /**
     * Build normalized member data array.
     * IMPORTANT: sex stored as 'M'/'F' to match enum column.
     */
    private function buildMemberData(array $data, string $householdId): array
    {
        // Handle birth date
        $birthDate = $data['birth_date'] ?? null;
        $age = null;
        
        if ($birthDate) {
            try {
                $age = (int) Carbon::parse($birthDate)->diffInYears(now());
            } catch (\Exception $e) {
                $age = null;
            }
        }
        
        // Accept both 'M'/'F' and 'male'/'female'
        $sexRaw = strtoupper(trim($data['sex'] ?? 'M'));
        $sex = in_array($sexRaw, ['M', 'F']) ? $sexRaw : (str_starts_with(strtolower($sexRaw), 'm') ? 'M' : 'F');
        // gender column stores 'male'/'female' display value
        $gender = $sex === 'M' ? 'male' : 'female';
        $isPwd = $this->booleanValue($data['is_pwd'] ?? false);

        return [
            'household_id'    => $householdId,
            'name'            => trim(
                $data['first_name'] . ' ' .
                (!empty($data['middle_name']) ? $data['middle_name'] . ' ' : '') .
                $data['last_name']
            ),
            'gender'          => $gender,
            'sex'             => $sex,         // M or F (matches enum)
            'age'             => $age,
            'special_needs'   => $isPwd ? 'pwd' : ($age !== null && $age >= 60 ? 'senior' : ($age !== null && $age < 18 ? 'child' : 'adult')),
            'first_name'      => $data['first_name'],
            'middle_name'     => $data['middle_name'] ?? null,
            'last_name'       => $data['last_name'],
            'birth_date'      => $birthDate,
            'civil_status'    => $data['civil_status']    ?? null,
            'education_level' => $data['education_level'] ?? null,
            'occupation'      => $data['occupation']      ?? null,
            'relation'        => $data['relation']        ?? null,
            'is_pwd'          => $isPwd,
            'is_pregnant'     => $this->booleanValue($data['is_pregnant'] ?? false),
            'is_graduate'     => false,
        ];
    }

    private function booleanValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtoupper(trim((string) $value)), ['1', 'Y', 'YES', 'TRUE', 'PWD'], true);
    }
}
