<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the request body for storing a new household via the API.
 */
class StoreHouseholdApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'household_name'            => 'required|string|max:100',
            'email'                     => 'nullable|email|max:150|unique:households,email|unique:users,email',
            'street'                    => 'nullable|string|max:255',
            'purok_sitio'               => 'nullable|string|max:150',
            'house_number'              => 'nullable|string|max:100',
            'zip_code'                  => 'nullable|string|max:20',
            'full_address'              => 'nullable|string|max:500',
            'barangay_id'               => 'nullable|exists:barangays,barangay_id',
            'barangay_name'             => 'nullable|string|max:100|required_without:barangay_id',
            'contact_number'            => 'nullable|string|max:50',
            'emergency_contact'         => 'nullable|string|max:50',
            'head_first_name'           => 'required|string|max:100',
            'head_middle_name'          => 'nullable|string|max:100',
            'head_last_name'            => 'required|string|max:100',
            'members'                   => 'nullable|array',
            'members.*.first_name'      => 'required_with:members|string|max:100',
            'members.*.middle_name'     => 'nullable|string|max:100',
            'members.*.last_name'       => 'required_with:members|string|max:100',
            'members.*.birth_date'      => 'required_with:members|date',
            'members.*.sex'             => 'required_with:members|in:M,F',
            'members.*.relation'        => 'nullable|string|max:50',
            'members.*.civil_status'    => 'nullable|string|max:50',
            'members.*.education_level' => 'nullable|string|max:100',
            'members.*.occupation'      => 'nullable|string|max:100',
            'members.*.is_pwd'          => 'nullable|boolean',
            'members.*.is_pregnant'     => 'nullable|boolean',
        ];
    }
}
