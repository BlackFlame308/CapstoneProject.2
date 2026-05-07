<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHouseholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->canManageHouseholds() ?? false;
    }

    public function rules(): array
    {
        return [
            // Household basics
            'household_name'             => 'required|string|max:100',
            'email'                      => 'nullable|email|max:150|unique:households,email|unique:users,email',
            'contact_number'             => 'nullable|string|max:20',
            'emergency_contact'          => 'nullable|string|max:20',

            // Address
            'street'                     => 'nullable|string|max:255',
            'purok_sitio'                => 'nullable|string|max:150',
            'full_address'               => 'nullable|string|max:500',
            'barangay_id'                => 'required|exists:barangays,id',

            // Household head (required)
            'head_first_name'            => 'required|string|max:100',
            'head_middle_name'           => 'nullable|string|max:100',
            'head_last_name'             => 'required|string|max:100',
            'head_birth_date'            => 'required|date|before:today',
            'head_sex'                   => 'required|in:M,F',
            'head_civil_status'          => 'nullable|string|max:50',
            'head_education_level'       => 'nullable|string|max:100',
            'head_occupation'            => 'nullable|string|max:100',
            'head_is_pwd'                => 'nullable|boolean',
            'head_is_pregnant'           => 'nullable|boolean',

            // Members array (optional but validated if present)
            'members'                    => 'nullable|array',
            'members.*.first_name'       => 'required_with:members|string|max:100',
            'members.*.middle_name'      => 'nullable|string|max:100',
            'members.*.last_name'        => 'required_with:members|string|max:100',
            'members.*.birth_date'       => 'required_with:members|date|before:today',
            'members.*.sex'              => 'required_with:members|in:M,F',
            'members.*.relation'         => 'nullable|string|max:50',
            'members.*.civil_status'     => 'nullable|string|max:50',
            'members.*.education_level'  => 'nullable|string|max:100',
            'members.*.occupation'       => 'nullable|string|max:100',
            'members.*.is_pwd'           => 'nullable|boolean',
            'members.*.is_pregnant'      => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'household_name.required' => 'Household name is required.',
            'barangay_id.required'    => 'Please select a barangay location.',
            'barangay_id.exists'      => 'Selected barangay is invalid.',
            'head_first_name.required'=> 'Household head first name is required.',
            'head_last_name.required' => 'Household head last name is required.',
            'head_birth_date.required'=> 'Household head birth date is required.',
            'head_birth_date.before'  => 'Household head birth date must be in the past.',
            'members.*.first_name.required_with' => 'Member first name is required.',
            'members.*.last_name.required_with'  => 'Member last name is required.',
            'members.*.birth_date.required_with' => 'Member birth date is required.',
            'members.*.sex.required_with'        => 'Member sex is required.',
            'members.*.birth_date.before'        => 'Birth date must be in the past.',
        ];
    }
}
