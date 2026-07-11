<?php

namespace App\Services\Csv;

use App\Models\Address;
use App\Models\Barangay;
use App\Models\Household;
use App\Models\Member;
use App\Services\HouseholdAccountService;
use App\Services\MemberDataBuilder;
use Carbon\Carbon;

/**
 * CsvRowParser
 *
 * Processes a single CSV row, resolving barangay, creating the address,
 * household, user account, and member record.
 */
class CsvRowParser
{
    private const BOOL_TRUE_VALUES = ['Y', '1', 'YES', 'TRUE', 'PWD'];

    public function __construct(
        private readonly string $dataSourceId,
        private readonly string $uploadedBy
    ) {}

    /**
     * Process a single CSV row.
     *
     * @param  array        $row
     * @param  array        $header     Normalized (lowercase) header array
     * @param  int          $rowNumber
     * @param  Household|null &$currentHousehold Tracking context for multi-row imports
     */
    public function processRow(array $row, array $header, int $rowNumber, ?Household &$currentHousehold): void
    {
        $get     = $this->columnGetter($row, $header);
        $boolVal = fn(string $v): bool => in_array(strtoupper(trim($v)), self::BOOL_TRUE_VALUES, true);

        $fields = $this->extractFields($get, $boolVal);

        if ($fields['isNewHousehold']) {
            $this->validateHeadName($fields, $rowNumber);
            $this->ensureUniqueCode($fields['householdCode'], $rowNumber);

            $fields = $this->applyHeadFallbacks($fields);

            $barangayId    = $this->resolveBarangay($fields['barangayValue']);
            $address       = $this->createAddress($fields, $barangayId);
            $household     = $this->createHousehold($fields, $address);

            (new HouseholdAccountService())->provision(
                $household,
                $fields['email'] ?: null,
                MemberDataBuilder::buildFullName([
                    'first_name'  => $fields['headFirstName'],
                    'middle_name' => $fields['headMiddleName'],
                    'last_name'   => $fields['headLastName'],
                ])
            );

            $currentHousehold = $household;
        } else {
            if (!$currentHousehold) {
                throw new \Exception("Row {$rowNumber}: Member row without an active household head context.");
            }
            $household = $currentHousehold;
        }

        $this->createMemberIfPresent($fields, $household, $rowNumber);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function columnGetter(array $row, array $header): \Closure
    {
        return function (array $names, int $fallbackIndex) use ($row, $header): string {
            foreach ($names as $name) {
                $idx = array_search($name, $header, true);
                if ($idx !== false && isset($row[$idx])) {
                    return trim($row[$idx]);
                }
            }
            return isset($row[$fallbackIndex]) ? trim($row[$fallbackIndex]) : '';
        };
    }

    private function extractFields(\Closure $get, \Closure $boolVal): array
    {
        return [
            'headFirstName'      => $get(['head_first_name', 'first_name'], 0),
            'headMiddleName'     => $get(['head_middle_name', 'middle_name'], 1),
            'headLastName'       => $get(['head_last_name', 'last_name'], 2),
            'headBirthDate'      => $get(['head_birth_date', 'birth_date'], 13),
            'headSex'            => strtoupper($get(['head_sex', 'sex'], 14) ?: 'M'),
            'headIsPwdRaw'       => $get(['head_is_pwd', 'head_pwd'], 19),
            'headIsPregnantRaw'  => $get(['head_is_pregnant'], 20),
            'householdCode'      => $get(['household_code', 'household_id'], 3),
            'householdName'      => $get(['household_name'], -1),
            'email'              => $get(['email'], 4),
            'street'             => $get(['street'], 5),
            'purokSitio'         => $get(['purok', 'purok_sitio'], 6),
            'barangayValue'      => $get(['barangay', 'barangay_id', 'barangay_name', 'barangay_id_or_name'], 7),
            'contactNumber'      => $get(['contact_number'], 8),
            'emergencyContact'   => $get(['emergency_contact'], 9),
            'memberFirstName'    => $get(['member_first_name'], 10),
            'memberMiddleName'   => $get(['member_middle_name'], 11),
            'memberLastName'     => $get(['member_last_name'], 12),
            'memberBirthDate'    => $get(['member_birth_date'], 13),
            'memberSex'          => strtoupper($get(['member_sex'], 14)),
            'memberRelation'     => $get(['member_relation'], 15),
            'memberCivilStatus'  => $get(['member_civil_status'], 16),
            'memberEducation'    => $get(['member_education_level'], 17),
            'memberOccupation'   => $get(['member_occupation', 'member_profession'], 18),
            'memberIsPwdRaw'     => $get(['member_is_pwd', 'is_pwd', 'pwd', 'person_with_disability'], 19),
            'memberIsPregnantRaw'=> $get(['member_is_pregnant', 'is_pregnant', 'pregnant'], 20),
            'isNewHousehold'     => !empty($get(['head_first_name', 'first_name'], 0)) ||
                                    !empty($get(['head_last_name', 'last_name'], 2)) ||
                                    !empty($get(['household_name'], -1)),
        ];
    }

    private function validateHeadName(array $fields, int $rowNumber): void
    {
        if (empty($fields['headFirstName']) || empty($fields['headLastName'])) {
            throw new \Exception("Row {$rowNumber}: missing household head first/last name");
        }
    }

    private function ensureUniqueCode(string $code, int $rowNumber): void
    {
        if (!empty($code) && Household::where('household_id', $code)->orWhere('household_code', $code)->exists()) {
            throw new \Exception("Row {$rowNumber}: household code '{$code}' already exists");
        }
    }

    private function applyHeadFallbacks(array $fields): array
    {
        if (empty($fields['householdCode'])) {
            $fields['householdCode'] = 'HH-' . strtoupper(\Illuminate\Support\Str::random(8));
        }

        $fields['memberFirstName']    = $fields['memberFirstName']    ?: $fields['headFirstName'];
        $fields['memberMiddleName']   = $fields['memberMiddleName']   ?: $fields['headMiddleName'];
        $fields['memberLastName']     = $fields['memberLastName']      ?: $fields['headLastName'];
        $fields['memberBirthDate']    = $fields['memberBirthDate']    ?: $fields['headBirthDate'];
        $fields['memberSex']          = $fields['memberSex']          ?: $fields['headSex'];
        $fields['memberRelation']     = $fields['memberRelation']     ?: 'Head';
        $fields['memberIsPwdRaw']     = $fields['memberIsPwdRaw']     ?: $fields['headIsPwdRaw'];
        $fields['memberIsPregnantRaw'] = $fields['memberIsPregnantRaw'] ?: $fields['headIsPregnantRaw'];

        return $fields;
    }

    private function createAddress(array $fields, ?int $barangayId): Address
    {
        return Address::create([
            'street'        => $fields['street']      ?: null,
            'purok_sitio'   => $fields['purokSitio']  ?: null,
            'barangay_id'   => $barangayId,
            'barangay_name' => (!$barangayId && $fields['barangayValue']) ? $fields['barangayValue'] : null,
        ]);
    }

    private function createHousehold(array $fields, Address $address): Household
    {
        return Household::create([
            'household_id'      => $fields['householdCode'],
            'household_code'    => $fields['householdCode'],
            'household_name'    => $fields['householdName']
                ?: trim($fields['headFirstName'] . ' ' . $fields['headLastName'] . ' Household'),
            'email'             => $fields['email'] ?: null,
            'member_count'      => 0,
            'address_id'        => $address->address_id,
            'contact_number'    => $fields['contactNumber']    ?: null,
            'emergency_contact' => $fields['emergencyContact'] ?: null,
            'created_by'        => $this->uploadedBy,
        ]);
    }

    private function createMemberIfPresent(array $fields, Household $household, int $rowNumber): void
    {
        $boolVal = fn(string $v) => in_array(strtoupper(trim($v)), self::BOOL_TRUE_VALUES, true);

        $hasData = !empty($fields['memberFirstName']) &&
                   !empty($fields['memberLastName'])  &&
                   !empty($fields['memberBirthDate']);

        if (!$hasData) return;

        $parsedDate = CsvDateParser::parse($fields['memberBirthDate']);
        if (!$parsedDate) {
            throw new \Exception("Row {$rowNumber}: invalid birth_date '{$fields['memberBirthDate']}'");
        }

        $sex = MemberDataBuilder::normalizeSex($fields['memberSex'] ?: 'M');

        $memberData = MemberDataBuilder::build([
            'first_name'      => $fields['memberFirstName'],
            'middle_name'     => $fields['memberMiddleName']  ?: null,
            'last_name'       => $fields['memberLastName'],
            'birth_date'      => $parsedDate,
            'sex'             => $sex,
            'relation'        => $fields['memberRelation']    ?: null,
            'civil_status'    => $fields['memberCivilStatus'] ?: null,
            'education_level' => $fields['memberEducation']   ?: null,
            'occupation'      => $fields['memberOccupation']  ?: null,
            'is_pwd'          => $boolVal($fields['memberIsPwdRaw']),
            'is_pregnant'     => $boolVal($fields['memberIsPregnantRaw']),
        ], $household->household_id);

        Member::create($memberData);
        $household->increment('member_count');

        \Log::info("Row {$rowNumber}: member {$memberData['name']} added to {$household->household_code}");
    }

    private function resolveBarangay(string $value): ?int
    {
        if (empty($value)) return null;

        if (is_numeric($value)) {
            $id = (int) $value;
            return Barangay::where('barangay_id', $id)->exists() ? $id : null;
        }

        $found = Barangay::where('name', 'like', $value)->value('barangay_id');
        return $found !== null ? (int) $found : null;
    }
}
