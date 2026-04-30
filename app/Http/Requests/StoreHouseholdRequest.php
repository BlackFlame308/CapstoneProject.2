<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHouseholdRequest extends FormRequest
{
public function authorize(): bool
    {
        // Captain and Encoder can create households
        return auth()->user()?->canManageHouseholds() ?? false;
    }

    public function rules(): array
    {
        return [
            'household_name'             => 'required|string|max:100',
            'email'                      => 'nullable|email|max:150|unique:households,email|unique:users,email',
            'street'                     => 'nullable|string|max:255',
            'purok_sitio'                => 'nullable|string|max:150',
            'barangay_id'                => 'required|exists:barangays,id',
            'contact_number'             => 'nullable|string|max:20',
            'emergency_contact'          => 'nullable|string|max:20',
            'head_first_name'            => 'required|string|max:100',
            'head_middle_name'           => 'nullable|string|max:100',
            'head_last_name'             => 'required|string|max:100',
            'members'                    => 'nullable|array',
            'members.*.first_name'       => 'required_with:members|string|max:100',
            'members.*.middle_name'      => 'nullable|string|max:100',
            'members.*.last_name'        => 'required_with:members|string|max:100',
            'members.*.birth_date'       => 'required_with:members|date',
            'members.*.sex'              => 'required_with:members|in:M,F',
            'members.*.relation'         => 'nullable|string|max:50',
            'members.*.civil_status'     => 'nullable|string|max:50',
            'members.*.education_level'  => 'nullable|string|max:100',
            'members.*.occupation'       => 'nullable|string|max:100',
            'members.*.is_pwd'           => 'nullable|boolean',
            'members.*.is_pregnant'      => 'nullable|boolean',
        ];
    }
}

