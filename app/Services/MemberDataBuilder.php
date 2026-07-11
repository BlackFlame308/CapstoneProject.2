<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * MemberDataBuilder
 *
 * Responsible for building a normalized member data array
 * suitable for creating or updating a Member record.
 * Shared by HouseholdService and HouseholdCsvImportService.
 */
class MemberDataBuilder
{
    /**
     * Build a normalized array for Member::create() or Member::update().
     *
     * @param array  $data        Raw member input data
     * @param string $householdId UUID of the parent household
     */
    public static function build(array $data, string $householdId): array
    {
        $birthDate = $data['birth_date'] ?? null;
        $age       = $birthDate ? static::calculateAge($birthDate) : null;

        $sex       = static::normalizeSex($data['sex'] ?? 'M');
        $gender    = $sex === 'M' ? 'male' : 'female';
        $isPwd     = static::parseBooleanValue($data['is_pwd'] ?? false);

        return [
            'household_id'    => $householdId,
            'name'            => static::buildFullName($data),
            'gender'          => $gender,
            'sex'             => $sex,
            'age'             => $age,
            'special_needs'   => static::resolveSpecialNeeds($isPwd, $age),
            'first_name'      => $data['first_name'],
            'middle_name'     => $data['middle_name'] ?? null,
            'last_name'       => $data['last_name'],
            'birth_date'      => $birthDate,
            'civil_status'    => $data['civil_status']    ?? null,
            'education_level' => $data['education_level'] ?? null,
            'occupation'      => $data['occupation']      ?? null,
            'relation'        => $data['relation']        ?? null,
            'is_pwd'          => $isPwd,
            'is_pregnant'     => static::parseBooleanValue($data['is_pregnant'] ?? false),
            'is_graduate'     => false,
        ];
    }

    public static function parseBooleanValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        return in_array(strtoupper(trim((string) $value)), ['1', 'Y', 'YES', 'TRUE', 'PWD'], true);
    }

    public static function normalizeSex(string $raw): string
    {
        $upper = strtoupper(trim($raw));
        if (in_array($upper, ['M', 'F'], true)) return $upper;
        return str_starts_with(strtolower($upper), 'm') ? 'M' : 'F';
    }

    public static function buildFullName(array $data): string
    {
        return trim(
            $data['first_name'] . ' ' .
            (!empty($data['middle_name']) ? $data['middle_name'] . ' ' : '') .
            $data['last_name']
        );
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private static function calculateAge(string $birthDate): ?int
    {
        try {
            return (int) Carbon::parse($birthDate)->diffInYears(now());
        } catch (\Throwable) {
            return null;
        }
    }

    private static function resolveSpecialNeeds(bool $isPwd, ?int $age): string
    {
        if ($isPwd) return 'pwd';
        if ($age !== null && $age >= 60) return 'senior';
        if ($age !== null && $age < 18) return 'child';
        return 'adult';
    }
}
