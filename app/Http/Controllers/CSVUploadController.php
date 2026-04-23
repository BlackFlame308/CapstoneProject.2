<?php

namespace App\Http\Controllers;

use App\Services\HouseholdCsvImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CSVUploadController extends Controller
{
    /**
     * Show CSV upload form
     */
    public function uploadForm()
    {
        abort_if(! auth()->user()->can('manage_households'), 403);

        try {
            return view('csv.upload');
        } catch (\Exception $e) {
            \Log::error('Error showing CSV upload form: ' . $e->getMessage());
            return back()->with('error', 'Failed to load upload form');
        }
    }

    /**
     * Process CSV upload
     */
    public function upload(Request $request, HouseholdCsvImportService $service)
    {
        abort_if(! auth()->user()->can('manage_households'), 403);

        try {
            // Validate
            $validated = $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            ]);

            if (!auth()->check()) {
                return redirect()->route('login')->with('error', 'User not authenticated');
            }

            // Get uploaded file directly from temp path (no storage needed)
            $file = $request->file('csv_file');
            
            if (!$file->isValid() || !$file->isReadable()) {
                return back()->with('error', 'Uploaded file is not valid or readable');
            }

            $tempFilePath = $file->getRealPath();
            \Log::info("Processing CSV from temp path: {$tempFilePath}");

            if (!file_exists($tempFilePath)) {
                return back()->with('error', 'Temporary file not accessible');
            }

            $result = $service->import($tempFilePath, auth()->id());

            return redirect()
                ->route('households.index')
                ->with('success', $result['message']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('CSV Validation Error: ' . json_encode($e->errors()));
            return back()->withErrors($e->errors())
                ->with('error', 'Validation failed');

        } catch (\Exception $e) {
            \Log::error('CSV Upload Error: ' . $e->getMessage());
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}