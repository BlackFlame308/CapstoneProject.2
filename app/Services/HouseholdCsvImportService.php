<?php

namespace App\Services;

use App\Models\CsvUpload;
use App\Models\DataSource;
use App\Models\ImportLog;
use App\Services\Csv\CsvRowParser;
use Illuminate\Support\Facades\DB;

/**
 * HouseholdCsvImportService
 *
 * Orchestrates the CSV import process: reads the file, iterates rows,
 * delegates parsing to CsvRowParser, and tracks import statistics.
 */
class HouseholdCsvImportService
{
    private DataSource $dataSource;
    private CsvUpload  $csvUpload;

    private int $totalRecords      = 0;
    private int $successfulRecords = 0;
    private int $failedRecords     = 0;

    /**
     * Import households from a CSV file.
     *
     * @throws \Exception if file is missing or empty
     */
    public function import(string $filePath, string $uploadedBy): array
    {
        @set_time_limit(300);

        if (!file_exists($filePath)) {
            throw new \Exception('CSV file not found at specified path');
        }

        $this->initializeTrackingRecords($filePath, $uploadedBy);
        $this->processCsv($filePath, $uploadedBy);
        $this->finalizeTrackingRecord();

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

    // ── Private methods ───────────────────────────────────────────────────────

    private function initializeTrackingRecords(string $filePath, string $uploadedBy): void
    {
        $this->dataSource = DataSource::create(['type' => 'csv', 'uploaded_by' => $uploadedBy]);
        $this->csvUpload  = CsvUpload::create([
            'data_source_id' => $this->dataSource->id,
            'file_name'      => basename($filePath),
        ]);
    }

    private function finalizeTrackingRecord(): void
    {
        $this->csvUpload->update([
            'total_records'      => $this->totalRecords,
            'successful_records' => $this->successfulRecords,
            'failed_records'     => $this->failedRecords,
        ]);
    }

    private function processCsv(string $filePath, string $uploadedBy): void
    {
        $file = fopen($filePath, 'r');
        if ($file === false) {
            throw new \Exception('Unable to open CSV file for reading');
        }

        try {
            $this->skipBom($file);

            $header = fgetcsv($file);
            if (empty($header)) {
                throw new \Exception('CSV file is empty or has invalid format');
            }

            $header          = array_map(fn($h) => strtolower(trim($h)), $header);
            $rowNumber       = 1;
            $currentHousehold = null;

            $parser = new CsvRowParser($this->dataSource->id, $uploadedBy);

            while (($row = fgetcsv($file)) !== false) {
                $rowNumber++;
                if (empty(array_filter($row))) continue;

                $this->processRow($parser, $row, $header, $rowNumber, $currentHousehold);
            }

            if ($this->totalRecords === 0) {
                throw new \Exception('CSV file contains no data rows (only a header)');
            }
        } finally {
            if (is_resource($file)) fclose($file);
        }
    }

    private function processRow(
        CsvRowParser $parser,
        array        $row,
        array        $header,
        int          $rowNumber,
        mixed        &$currentHousehold
    ): void {
        $this->totalRecords++;

        try {
            DB::transaction(function () use ($parser, $row, $header, $rowNumber, &$currentHousehold) {
                $parser->processRow($row, $header, $rowNumber, $currentHousehold);
            });
            $this->successfulRecords++;
            ImportLog::create([
                'data_source_id' => $this->dataSource->id,
                'row_number'     => $rowNumber,
                'status'         => 'success',
            ]);
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

    private function skipBom($file): void
    {
        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($file);
        }
    }
}
