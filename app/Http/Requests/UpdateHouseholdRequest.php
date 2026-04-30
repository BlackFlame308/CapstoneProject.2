<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHouseholdRequest extends FormRequest
{
public function authorize(): bool
    {
        return auth()->user()?->canManageHouseholds() ?? false;
    }

    public function rules(): array
    {
        return [
            'household_name'             => 'required|string|max:100',
            'email'                      => 'nullable|email|max:150|unique:households,email,' . $this->route('household'),
            'contact_number'             => 'nullable|string|max:20',
            'emergency_contact'          => 'nullable|string|max:20',
            'street'                     => 'nullable|string|max:255',
            'purok_sitio'                => 'nullable|string|max:150',
            'barangay_id'                => 'required|exists:barangays,id',
            'members'                    => 'nullable|array',
            'members.*.id'               => 'nullable|exists:members,id',
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

