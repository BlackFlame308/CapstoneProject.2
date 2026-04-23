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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class HouseholdCsvImportService
{
    private $dataSource;
    private $csvUpload;
    private $totalRecords = 0;
    private $successfulRecords = 0;
    private $failedRecords = 0;

    /**
     * Import households from CSV file within transaction
     */
    public function import($filePath, $uploadedBy)
    {
        return DB::transaction(function () use ($filePath, $uploadedBy) {
            try {
                // Validate file exists
                if (!file_exists($filePath)) {
                    throw new \Exception('CSV file not found at specified path');
                }

                // Validate uploaded by user exists
                if (!$uploadedBy) {
                    throw new \Exception('Invalid user context for import');
                }

                // Create data source
                $this->dataSource = DataSource::create([
                    'type' => 'csv',
                    'uploaded_by' => $uploadedBy,
                ]);

                // Create CSV upload record
                $this->csvUpload = CsvUpload::create([
                    'data_source_id' => $this->dataSource->id,
                    'file_name' => basename($filePath),
                ]);

                // Read and process CSV
                $this->processCsv($filePath, $uploadedBy);

                // Update CSV upload with stats
                $this->csvUpload->update([
                    'total_records' => $this->totalRecords,
                    'successful_records' => $this->successfulRecords,
                    'failed_records' => $this->failedRecords,
                ]);

                \Log::info("CSV Import completed: Total={$this->totalRecords}, Success={$this->successfulRecords}, Failed={$this->failedRecords}");

                return [
                    'success' => true,
                    'message' => "Import completed. {$this->successfulRecords} successful, {$this->failedRecords} failed.",
                    'stats' => [
                        'total' => $this->totalRecords,
                        'success' => $this->successfulRecords,
                        'failed' => $this->failedRecords,
                    ]
                ];
            } catch (\Exception $e) {
                \Log::error('CSV Import Error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Process CSV file line by line
     */
    private function processCsv($filePath, $uploadedBy)
    {
        if (!file_exists($filePath)) {
            throw new \Exception("CSV file not found: {$filePath}");
        }

        \Log::info("Processing CSV file: {$filePath}, Size: " . filesize($filePath));

        $file = fopen($filePath, 'r');
        
        if ($file === false) {
            throw new \Exception('Unable to open CSV file for reading');
        }

        try {
            $header = fgetcsv($file); // Skip header
            
            if (empty($header)) {
                throw new \Exception('CSV file is empty or invalid format');
            }

            $rowNumber = 1;

            while (($row = fgetcsv($file)) !== false) {
                $rowNumber++;
                $this->totalRecords++;

                try {
                    $this->processRow($row, $rowNumber, $uploadedBy);
                    $this->successfulRecords++;
                } catch (\Exception $e) {
                    $this->failedRecords++;
                    \Log::warning("CSV Row {$rowNumber} failed: " . $e->getMessage());
                    
                    ImportLog::create([
                        'data_source_id' => $this->dataSource->id,
                        'row_number' => $rowNumber,
                        'status' => 'failed',
                        'error_message' => substr($e->getMessage(), 0, 255),
                    ]);
                }
            }

            if ($this->totalRecords === 0) {
                throw new \Exception('CSV file contains no data rows (only header)');
            }
        } finally {
            if (is_resource($file)) {
                fclose($file);
            }
        }
    }

    /**
     * Process individual CSV row
     */
    private function processRow($row, $rowNumber, $uploadedBy)
    {
        // Expected CSV columns: 0-7 head/addr, 8-16 member
        $headFirstName = trim($row[0] ?? '');
        $headMiddleName = trim($row[1] ?? '');
        $headLastName = trim($row[2] ?? '');
        $street = trim($row[3] ?? '');
        $purok = trim($row[4] ?? '');
        $barangayIdOrName = trim($row[5] ?? '');
        $contactNumber = trim($row[6] ?? '');
        $emergencyContact = trim($row[7] ?? '');

        $memberFirstName = trim($row[8] ?? '');
        $memberMiddleName = trim($row[9] ?? '');
        $memberLastName = trim($row[10] ?? '');
        $memberBirthDate = trim($row[11] ?? '');
        $memberSex = strtoupper(trim($row[12] ?? 'M'));
        $memberCivilStatus = trim($row[13] ?? '');
        $memberEducation = trim($row[14] ?? '');
        $memberProfession = trim($row[15] ?? '');
        $memberIsPwd = strtoupper(trim($row[16] ?? 'N')) === 'Y';

        if (empty($headFirstName) || empty($headLastName)) {
            throw new \Exception('Missing head name in row ' . $rowNumber);
        }

        $resolvedBarangayId = $this->resolveBarangay($barangayIdOrName);
        if (!$resolvedBarangayId) {
            throw new \Exception("Barangay '{$barangayIdOrName}' not found in row " . $rowNumber);
        }

        $address = Address::create([
            'street' => $street ?: null,
            'purok' => $purok ?: null,
            'barangay_id' => $resolvedBarangayId,
        ]);

        $householdCode = 'HH-' . strtoupper(Str::random(8));
        $household = Household::create([
            'household_code' => $householdCode,
            'address_id' => $address->id,
            'contact_number' => $contactNumber ?: null,
            'emergency_contact' => $emergencyContact ?: null,
            'created_by' => $uploadedBy,
        ]);

        $householdRole = Role::where('name', 'Household')->first();
        if (!$householdRole) {
            throw new \Exception('Run seeder for Household role');
        }

        $tempPassword = Str::password(12);
        $user = User::create([
            'name' => trim("{$headFirstName} " . ($headMiddleName ? $headMiddleName . ' ' : '') . $headLastName),
            'email' => strtolower("{$householdCode}@capstone.local"),
            'password' => bcrypt($tempPassword),
            'role_id' => $householdRole->id,
            'household_id' => $household->id,
            'must_change_password' => true,
            'temp_password' => $tempPassword,
        ]);

        \Log::info("Row {$rowNumber}: Created HH {$householdCode}, temp pass {$tempPassword}");

        // Always try to create member if data present (even if first column empty but others have data)
        if ($memberLastName || $memberBirthDate) {
            if (empty($memberFirstName) || empty($memberLastName) || empty($memberBirthDate)) {
                \Log::warning("Row {$rowNumber}: Skipping incomplete member");
            } else {
                $formattedBirthDate = $this->parseAndFormatDate($memberBirthDate);
                if (!$formattedBirthDate) {
                    throw new \Exception("Row {$rowNumber}: Invalid member birth date '{$memberBirthDate}'");
                }

                Member::create([
                    'household_id' => $household->id,
                    'first_name' => $memberFirstName,
                    'middle_name' => $memberMiddleName,
                    'last_name' => $memberLastName,
                    'birth_date' => $formattedBirthDate,
                    'sex' => $memberSex,
                    'civil_status' => $memberCivilStatus,
                    'education_level' => $memberEducation,
                    'profession' => $memberProfession,
                    'is_pwd' => $memberIsPwd,
                ]);
                \Log::info("Row {$rowNumber}: Added member {$memberFirstName} {$memberLastName}");
            }
        }

        ImportLog::create([
            'data_source_id' => $this->dataSource->id,
            'row_number' => $rowNumber,
            'status' => 'success',
        ]);
    }

    /**
     * Resolve barangay ID from numeric ID or exact name
     */
    private function resolveBarangay($value)
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            $barangay = Barangay::find((int)$value);
            return $barangay ? (int)$value : null;
        }

        // Exact match name
        $barangay = Barangay::where('name', trim($value))->first();
        return $barangay ? $barangay->id : null;
    }

    /**
     * Parse and format date to Y-m-d format
     * Supports DD/MM/YYYY and YYYY-MM-DD formats
     */
    private function parseAndFormatDate($date)
    {
        $date = trim($date);
        
        if (empty($date)) {
            return null;
        }

        // Try DD/MM/YYYY format first
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date, $matches)) {
            $day = (int)$matches[1];
            $month = (int)$matches[2];
            $year = (int)$matches[3];
            
            if ($day >= 1 && $day <= 31 && $month >= 1 && $month <= 12 && $year >= 1900 && $year <= 2100) {
                try {
                    $dateObj = \DateTime::createFromFormat('d/m/Y', $date);
                    return $dateObj ? $dateObj->format('Y-m-d') : null;
                } catch (\Exception $e) {
                    return null;
                }
            }
        }

        // Try YYYY-MM-DD format
        if ($this->isValidDate($date, 'Y-m-d')) {
            return $date;
        }

        return null;
    }

    /**
     * Validate date format (YYYY-MM-DD)
     */
    private function isValidDate($date, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}

