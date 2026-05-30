<?php

namespace App\Services;

use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/**
 * HouseholdAccountService
 *
 * Single responsibility: automatically provision a Household-role user account
 * whenever a new household is created — whether from the manual form or CSV import.
 *
 * Returns an array with the created user and the plain-text temporary password,
 * or null if provisioning was skipped (role not found, already exists, etc.).
 */
class HouseholdAccountService
{
    /**
     * Provision a user account for the given household.
     *
     * @param  Household  $household
     * @param  string|null  $preferredEmail  Email from form / CSV (may be null or already taken)
     * @param  string|null  $householdHeadName  Full name of the household head (optional)
     * @return array{user: User, password: string}|null
     */
    public function provision(
        Household $household,
        ?string   $preferredEmail   = null,
        ?string   $householdHeadName = null
    ): ?array {
        // Skip if a user is already linked to this household
        if (User::where('household_id', $household->household_id)->exists()) {
            return null;
        }

        $householdRole = Role::whereRaw('LOWER(name) = ?', ['household'])->first();
        if (!$householdRole) {
            \Log::warning("HouseholdAccountService: 'Household' role not found — account not created for {$household->household_id}");
            return null;
        }

        // ── Username: derive from household_code, ensure unique ──
        $base     = Str::slug($household->household_code ?? ('hh-' . Str::random(6)), '_');
        $username = $base;
        $counter  = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . '_' . $counter++;
        }

        // ── Email: prefer provided email, fall back to auto-generated ──
        $email = $preferredEmail;
        if (empty($email) || User::where('email', $email)->exists()) {
            $safeCode = Str::lower(preg_replace('/[^a-z0-9]/i', '', $household->household_code ?? $household->household_id));
            $email    = $safeCode . Str::lower(Str::random(4)) . '@safetrack.local';
        }

        // ── Name: household head name, fallback to household name ──
        $name = $householdHeadName ?: ($household->household_name ?: ('Household ' . $household->household_code));

        // ── Password: strong generated password ──
        $tempPassword = Str::upper(Str::random(6)) . random_int(10, 99) . Str::lower(Str::random(4));

        $user = User::create([
            'name'                 => $name,
            'username'             => $username,
            'email'                => $email,
            'contact_number'       => $household->contact_number,
            'password'             => Hash::make($tempPassword),
            'role_id'              => $householdRole->role_id,
            'household_id'         => $household->household_id,
            'is_active'            => true,
            'must_change_password' => true,
            'temp_password'        => $tempPassword,
        ]);

        \Log::info("HouseholdAccountService: provisioned account '{$username}' for household {$household->household_id}");

        return ['user' => $user, 'password' => $tempPassword];
    }
}
