<?php

namespace App\Http\Controllers;

use App\Services\HouseholdCsvImportService;
use Illuminate\Http\Request;

class CSVUploadController extends Controller
{
    /**
     * Show CSV upload form
     */
    public function uploadForm()
    {
        try {
            return view('csv.upload');
        } catch (\Exception $e) {
            \Log::error('Error showing CSV upload form: ' . $e->getMessage());
            return back()->with('error', 'Failed to load upload form');
        }
    }

    /**
     * Process CSV file upload
     */
    public function upload(Request $request)
    {
        try {
            // Validate file
            $validated = $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            ], [
                'csv_file.required' => 'Please select a CSV file to upload',
                'csv_file.mimes' => 'File must be CSV or TXT format',
                'csv_file.max' => 'File size cannot exceed 10MB'
            ]);

            if (!$request->user()) {
                return redirect()->route('login')->with('error', 'User not authenticated');
            }

            $file = $request->file('csv_file');
            $filePath = $file->store('csv_uploads', 'local');
            $fullPath = storage_path('app/' . $filePath);

            // Verify file exists before importing
            if (!file_exists($fullPath)) {
                throw new \Exception('Uploaded file not found');
            }

            // Import using service
            $service = new HouseholdCsvImportService();
            $result = $service->import($fullPath, $request->user()->id);

            // Safe file deletion
            try {
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to delete uploaded CSV file: ' . $e->getMessage());
            }

            return redirect()->route('households.index')->with('success', $result['message']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('CSV Upload Validation Error: ' . json_encode($e->errors()));
            return redirect()->back()->withErrors($e->errors())->with('error', 'Validation failed');
        } catch (\Exception $e) {
            \Log::error('CSV Upload Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
