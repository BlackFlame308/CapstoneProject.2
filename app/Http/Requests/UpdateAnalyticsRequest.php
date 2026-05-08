<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnalyticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Allow Captain (and Super Admin) to refresh analytics
        return auth()->user()?->isCaptain() || auth()->user()?->isSuperAdmin() ? true : false;
    }

    public function rules(): array
    {
        return [
            'location_ids'   => 'sometimes|array',
            'location_ids.*' => 'string|exists:barangays,id',
        ];
    }
}