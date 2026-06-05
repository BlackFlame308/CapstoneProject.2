<?php

namespace App\Services;

use App\Models\Household;
use App\Models\Member;
use App\Models\Address;
use App\Models\Barangay;
use App\Models\DataSource;
use App\Models\CsvUpload;
use App\Models\ImportLog;
use App\Models\User;
use App\Models\Role;
use App\Services\HouseholdAccountService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class HouseholdCsvImportService
{
    private $dataSource;
    private $csvUpload;
    private int $totalRecords    = 0;
    private int $successfulRecords = 0;
    private int $failedRecords   = 0;

    /**
     * Import households from CSV file.
     */
    public function import(string $filePath, string $uploadedBy): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception('CSV file not found at specified path');
        }

        DB::transaction(function () use ($filePath, $uploadedBy) {
            $this->dataSource = DataSource::create([
                'type'        => 'csv',
                'uploaded_by' => $uploadedBy,
            ]);

            $this->csvUpload = CsvUpload::create([
                'data_source_id' => $this->dataSource->id,
                'file_name'      => basename($filePath),
            ]);

            $this->processCsv($filePath, $uploadedBy);

            $this->csvUpload->update([
                'total_records'      => $this->totalRecords,
                'successful_records' => $this->successfulRecords,
                'failed_records'     => $this->failedRecords,
            ]);
        });

        \Log::info("CSV Import done: total={$this->totalRecords} success={$this->successfulRecords} failed={$this->failedRecords}");

        return [
            'success' => true,
            'message' => "Import completed: {$this->successfulRecords} uploaded, {$this->failedRecords} failed.",
            'stats'   => [
                'total'   => $this->totalRecords,
                'success' => $this->successfulRecords,
                'failed'  => $this->failedRecords,
            ],
        ];
    }

    private function processCsv(string $filePath, string $uploadedBy): void
    {
        $file = fopen($filePath, 'r');
        if ($file === false) {
            throw new \Exception('Unable to open CSV file for reading');
        }

        try {
            // Skip BOM if present
            $bom = fread($file, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($file);
            }

            $header = fgetcsv($file);
            if (empty($header)) {
                throw new \Exception('CSV file is empty or has invalid format');
            }

            // Normalize header keys (trim whitespace, lowercase)
            $header = array_map(fn($h) => strtolower(trim($h)), $header);

            $rowNumber = 1;
            $currentHousehold = null;

            while (($row = fgetcsv($file)) !== false) {
                $rowNumber++;

                if (empty(array_filter($row))) {
                    continue;
                }

                $this->totalRecords++;

                try {
                    DB::transaction(function () use ($row, $header, $rowNumber, $uploadedBy, &$currentHousehold) {
                        $this->processRow($row, $header, $rowNumber, $uploadedBy, $currentHousehold);
                    });
                    $this->successfulRecords++;
                } catch (\Throwable $e) {
                    $this->failedRecords++;
                    \Log::warning("CSV Row {$rowNumber} failed: " . $e->getMessage());

                    ImportLog::create([
                        'data_source_id' => $this->dataSource->id,
                        'row_number'     => $rowNumber,
                        'status'         => 'failed',
                        'error_message'  => substr($e->getMessage(), 0, 255),
                    ]);
                }
            }

            if ($this->totalRecords === 0) {
                throw new \Exception('CSV file contains no data rows (only a header)');
            }
        } finally {
            if (is_resource($file)) {
                fclose($file);
            }
        }
    }

    /**
     * Process one CSV row.
     * Supports both positional (index) and named column access.
     *
     * Expected columns (can be named or positional):
     *  0/head_first_name, 1/head_middle_name, 2/head_last_name,
     *  3/household_name,  4/email,            5/street,
     *  6/purok,           7/barangay,         8/contact_number,
     *  9/emergency_contact,
     * 10/member_first_name, 11/member_middle_name, 12/member_last_name,
     * 13/member_birth_date, 14/member_sex (M/F),
     * 15/member_relation,   16/member_civil_status,
     * 17/member_education_level, 18/member_occupation,
     * 19/member_is_pwd (Y/N/1/0), 20/member_is_pregnant (Y/N/1/0)
     */
    private function processRow(array $row, array $header, int $rowNumber, string $uploadedBy, ?Household &$currentHousehold): void
    {
        // Helper to get value by name or index
        $get = function (array $names, int $fallbackIndex) use ($row, $header): string {
            foreach ($names as $name) {
                $idx = array_search($name, $header, true);
                if ($idx !== false && isset($row[$idx])) {
                    return trim($row[$idx]);
                }
            }
            return isset($row[$fallbackIndex]) ? trim($row[$fallbackIndex]) : '';
        };

        $boolVal = fn(string $v): bool => in_array(strtoupper(trim($v)), ['Y', '1', 'YES', 'TRUE', 'PWD'], true);

        $headFirstName    = $get(['head_first_name', 'first_name'],   0);
        $headMiddleName   = $get(['head_middle_name', 'middle_name'],  1);
        $headLastName     = $get(['head_last_name', 'last_name'],      2);
        $headBirthDate    = $get(['head_birth_date', 'birth_date'],   13);
        $headSexRaw       = strtoupper($get(['head_sex', 'sex'],      14) ?: 'M');
        $headIsPwdRaw     = $get(['head_is_pwd', 'head_pwd'],         19);
        $headIsPregnantRaw = $get(['head_is_pregnant'],               20);
        $householdCode    = $get(['household_code', 'household_id'],   3);
        $householdName    = $get(['household_name'],                  -1); // Search by name only, fallback to head's name below
        $email            = $get(['email'],                            4);
        $street           = $get(['street'],                           5);
        $purokSitio       = $get(['purok', 'purok_sitio'],             6);
        $barangayValue    = $get(['barangay', 'barangay_id', 'barangay_name', 'barangay_id_or_name'], 7);
        $contactNumber    = $get(['contact_number'],                   8);
        $emergencyContact = $get(['emergency_contact'],                9);

        $memberFirstName   = $get(['member_first_name'],               10);
        $memberMiddleName  = $get(['member_middle_name'],              11);
        $memberLastName    = $get(['member_last_name'],                12);
        $memberBirthDate   = $get(['member_birth_date'],               13);
        $memberSexRaw      = strtoupper($get(['member_sex'],           14));
        $memberRelation    = $get(['member_relation'],                 15);
        $memberCivilStatus = $get(['member_civil_status'],             16);
        $memberEducation   = $get(['member_education_level'],          17);
        $memberOccupation  = $get(['member_occupation', 'member_profession'], 18);
        $memberIsPwdRaw    = $get(['member_is_pwd', 'is_pwd', 'pwd', 'person_with_disability'], 19);
        $memberIsPregnantRaw = $get(['member_is_pregnant', 'is_pregnant', 'pregnant'], 20);

        $isNewHousehold = !empty($headFirstName) || !empty($headLastName) || !empty($householdName);

        if ($isNewHousehold) {
            if (empty($householdCode)) {
                $householdCode = 'HH-' . strtoupper(\Illuminate\Support\Str::random(8));
            }
            if (empty($headFirstName) || empty($headLastName)) {
                throw new \Exception("Row {$rowNumber}: missing household head first/last name");
            }
            if (Household::where('household_id', $householdCode)->orWhere('household_code', $householdCode)->exists()) {
                throw new \Exception("Row {$rowNumber}: household code '{$householdCode}' already exists");
            }

            // Apply fallbacks for member details on head row
            $memberFirstName   = $memberFirstName ?: $headFirstName;
            $memberMiddleName  = $memberMiddleName ?: $headMiddleName;
            $memberLastName    = $memberLastName ?: $headLastName;
            $memberBirthDate   = $memberBirthDate ?: $headBirthDate;
            $memberSexRaw      = $memberSexRaw ?: $headSexRaw;
            $memberRelation    = $memberRelation ?: 'Head';
            $memberIsPwdRaw    = $memberIsPwdRaw ?: $headIsPwdRaw;
            $memberIsPregnantRaw = $memberIsPregnantRaw ?: $headIsPregnantRaw;

            // Resolve barangay
            $barangayId = $this->resolveBarangay($barangayValue);

            // Address
            $address = Address::create([
                'street'        => $street      ?: null,
                'purok_sitio'   => $purokSitio  ?: null,
                'barangay_id'   => $barangayId,
                'barangay_name' => (!$barangayId && $barangayValue) ? $barangayValue : null,
            ]);

            // Household
            $household = Household::create([
                'household_id'      => $householdCode,
                'household_code'    => $householdCode,
                'household_name'    => $householdName ?: trim($headFirstName . ' ' . $headLastName . ' Household'),
                'email'             => $email ?: null,
                'member_count'      => 0,
                'address_id'        => $address->address_id,
                'contact_number'    => $contactNumber    ?: null,
                'emergency_contact' => $emergencyContact ?: null,
                'created_by'        => $uploadedBy,
            ]);

            // Automatically provision Household user account via shared service
            $accountService = new HouseholdAccountService();
            $accountService->provision(
                $household,
                $email ?: null,
                trim($headFirstName . ' ' . ($headMiddleName ? $headMiddleName . ' ' : '') . $headLastName)
            );

            // Set as current context
            $currentHousehold = $household;
        } else {
            if (!$currentHousehold) {
                throw new \Exception("Row {$rowNumber}: Member row without an active household head context.");
            }
            $household = $currentHousehold;
        }

        $memberIsPwd      = $boolVal($memberIsPwdRaw);
        $memberIsPregnant = $boolVal($memberIsPregnantRaw);

        // Normalize sex: must be M or F for the enum column
        $memberSexRaw = strtoupper($memberSexRaw ?: 'M');
        $memberSex = in_array($memberSexRaw, ['M', 'F']) ? $memberSexRaw
            : (str_starts_with($memberSexRaw, 'M') ? 'M' : 'F');

        // Member (from this row)
        $hasMemberData = !empty($memberFirstName) && !empty($memberLastName) && !empty($memberBirthDate);

        if ($hasMemberData) {
            $parsedDate = $this->parseBirthDate($memberBirthDate);
            if (!$parsedDate) {
                throw new \Exception("Row {$rowNumber}: invalid birth_date '{$memberBirthDate}'");
            }

            $age          = (int) Carbon::parse($parsedDate)->diffInYears(now());
            $gender       = $memberSex === 'M' ? 'male' : 'female';
            $specialNeeds = $memberIsPwd ? 'pwd'
                : ($age >= 60 ? 'senior' : ($age < 18 ? 'child' : 'adult'));

            $memberFullName = trim(
                $memberFirstName . ' ' .
                ($memberMiddleName ? $memberMiddleName . ' ' : '') .
                $memberLastName
            );

            Member::create([
                'household_id'    => $household->household_id,
                'name'            => $memberFullName,
                'gender'          => $gender,
                'sex'             => $memberSex,   // M or F (matches enum)
                'age'             => $age,
                'relation'        => $memberRelation    ?: null,
                'special_needs'   => $specialNeeds,
                'first_name'      => $memberFirstName,
                'middle_name'     => $memberMiddleName  ?: null,
                'last_name'       => $memberLastName,
                'birth_date'      => $parsedDate,
                'civil_status'    => $memberCivilStatus ?: null,
                'education_level' => $memberEducation   ?: null,
                'occupation'      => $memberOccupation  ?: null,
                'is_pwd'          => $memberIsPwd,
                'is_pregnant'     => $memberIsPregnant,
                'is_graduate'     => false,
            ]);

            // Update member count
            $household->increment('member_count');

            \Log::info("Row {$rowNumber}: member {$memberFullName} added to {$household->household_code}");
        }

        ImportLog::create([
            'data_source_id' => $this->dataSource->id,
            'row_number'     => $rowNumber,
            'status'         => 'success',
        ]);

        \Log::info("Row {$rowNumber}: household {$household->household_code} processed successfully");
    }

    /**
     * Resolve barangay by integer ID or case-insensitive name.
     */
    private function resolveBarangay(string $value): ?int
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            $id = (int) $value;
            return Barangay::where('barangay_id', $id)->exists() ? $id : null;
        }

        $found = Barangay::where('name', 'like', $value)->value('barangay_id');
        return $found !== null ? (int) $found : null;
    }

    /**
     * Parse various birth date formats into Y-m-d.
     */
    private function parseBirthDate(string $raw): ?string
    {
        $raw = trim($raw);
        if (empty($raw)) {
            return null;
        }

        // MM/DD/YYYY
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $raw, $m)) {
            if (checkdate((int)$m[1], (int)$m[2], (int)$m[3]) && (int)$m[3] >= 1900) {
                return \DateTime::createFromFormat('m/d/Y', $raw)?->format('Y-m-d');
            }
        }

        // YYYY-MM-DD
        if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $raw)) {
            $date = \DateTime::createFromFormat('Y-m-d', $raw);
            return ($date && $date->format('Y-m-d') === $raw) ? $raw : null;
        }

        // DD/MM/YYYY fallback
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $raw, $m)) {
            if (checkdate((int)$m[2], (int)$m[1], (int)$m[3])) {
                return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
            }
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
