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

class HouseholdCsvImportService
{
    private $dataSource;
    private $csvUpload;
    private $totalRecords = 0;
    private $successfulRecords = 0;
    private $failedRecords = 0;

    /**
     * Import households from CSV file
     */
    public function import($filePath, $uploadedBy)
    {
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
    }

    /**
     * Process CSV file line by line
     */
    private function processCsv($filePath, $uploadedBy)
    {
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

            if ($this->totalRecords === 1) {
                throw new \Exception('CSV file contains only header row, no data to import');
            }
        } finally {
            fclose($file);
        }
    }

    /**
     * Process individual CSV row
     */
    private function processRow($row, $rowNumber, $uploadedBy)
    {
        // Expected columns: 
        // head_first_name, head_middle_name, head_last_name, 
        // street, purok, barangay_id, contact_number, emergency_contact,
        // member_first_name, member_middle_name, member_last_name, member_birth_date, member_sex, member_civil_status, etc.

        $headFirstName = trim($row[0] ?? '');
        $headMiddleName = trim($row[1] ?? '');
        $headLastName = trim($row[2] ?? '');
        $street = trim($row[3] ?? '');
        $purok = trim($row[4] ?? '');
        $barangayId = trim($row[5] ?? '');
        $contactNumber = trim($row[6] ?? '');
        $emergencyContact = trim($row[7] ?? '');

        // Validate required fields
        if (empty($headFirstName) || empty($headLastName)) {
            throw new \Exception('Missing required fields: head_first_name and head_last_name');
        }

        if (empty($barangayId) || !is_numeric($barangayId)) {
            throw new \Exception('Invalid barangay_id: must be numeric');
        }

        // Verify barangay exists
        $barangay = Barangay::find((int)$barangayId);
        if (!$barangay) {
            throw new \Exception("Barangay ID {$barangayId} not found");
        }

        try {
            // Create address
            $address = Address::create([
                'street' => $street ?: null,
                'purok' => $purok ?: null,
                'barangay_id' => (int)$barangayId,
            ]);

            // Generate household code
            $householdCode = 'HH-' . strtoupper(Str::random(8));

            // Create household
            $household = Household::create([
                'household_code' => $householdCode,
                'address_id' => $address->id,
                'contact_number' => $contactNumber ?: null,
                'emergency_contact' => $emergencyContact ?: null,
                'created_by' => $uploadedBy,
            ]);

            // Get household role
            $householdRole = Role::where('name', 'Household')->first() ?? Role::where('id', 3)->first();
            if (!$householdRole) {
                throw new \Exception('Household role not found in system');
            }

            // Create user account for household
            $username = strtolower(str_replace(' ', '_', $headFirstName . '_' . $headLastName));
            $tempPassword = 'Temp_' . Str::random(8);

            User::create([
                'name' => "{$headFirstName} {$headLastName}",
                'email' => $username . '@household.local',
                'password' => bcrypt($tempPassword),
                'role_id' => $householdRole->id,
                'household_id' => $household->id,
                'must_change_password' => true,
            ]);

            // Process additional members (if data provided)
            // Columns 8+: member data (first_name, middle_name, last_name, birth_date, sex, civil_status, education_level, profession, is_pwd)
            $memberData = array_slice($row, 8);
            if (!empty($memberData[0])) { // If member first name provided
                $memberFirstName = trim($memberData[0] ?? '');
                $memberLastName = trim($memberData[2] ?? '');
                $memberBirthDate = trim($memberData[3] ?? '');

                // Validate member required fields
                if (empty($memberFirstName) || empty($memberLastName)) {
                    throw new \Exception('Member data missing required fields: first_name and last_name');
                }

                if (empty($memberBirthDate)) {
                    throw new \Exception('Member data missing required field: birth_date');
                }

                // Validate birth date format
                if (!$this->isValidDate($memberBirthDate)) {
                    throw new \Exception("Invalid birth_date format: {$memberBirthDate}");
                }

                Member::create([
                    'household_id' => $household->id,
                    'first_name' => $memberFirstName,
                    'middle_name' => trim($memberData[1] ?? ''),
                    'last_name' => $memberLastName,
                    'birth_date' => $memberBirthDate,
                    'sex' => strtoupper(($memberData[4] ?? 'M')) === 'F' ? 'F' : 'M',
                    'civil_status' => trim($memberData[5] ?? '') ?: null,
                    'education_level' => trim($memberData[6] ?? '') ?: null,
                    'profession' => trim($memberData[7] ?? '') ?: null,
                    'is_pwd' => strtoupper(($memberData[8] ?? 'N')) === 'Y',
                ]);
            }

            ImportLog::create([
                'data_source_id' => $this->dataSource->id,
                'row_number' => $rowNumber,
                'status' => 'success',
            ]);

        } catch (\Exception $e) {
            // Clean up address if household creation failed
            if (isset($address)) {
                try {
                    $address->delete();
                } catch (\Exception $deleteError) {
                    \Log::warning("Failed to clean up orphaned address: " . $deleteError->getMessage());
                }
            }
            throw $e;
        }
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
