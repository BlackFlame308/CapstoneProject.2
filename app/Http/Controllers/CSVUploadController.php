<?php

namespace App\Http\Controllers;

use App\Services\HouseholdCsvImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CSVUploadController extends Controller
{
    /**
     * Show CSV upload form.
     */
    public function uploadForm()
    {
        $this->authorize('create', \App\Models\Household::class);

        return Inertia::render('CSV/Upload');
    }

    /**
     * Process CSV upload.
     */
    public function upload(Request $request, HouseholdCsvImportService $service)
    {
        $this->authorize('create', \App\Models\Household::class);

        try {
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            ]);

            $file = $request->file('csv_file');

            if (!$file->isValid() || !$file->isReadable()) {
                return back()->with('error', 'Uploaded file is not valid or readable.');
            }

            $tempFilePath = $file->getRealPath();

            if (!file_exists($tempFilePath)) {
                return back()->with('error', 'Temporary file could not be accessed. Please try again.');
            }

            Log::info("Processing CSV from temp path: {$tempFilePath}");

            $result = $service->import($tempFilePath, auth()->id());

            return redirect()
                ->route('households.index')
                ->with('success', $result['message']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->with('error', 'Please fix the validation errors and try again.');

        } catch (\Exception $e) {
            Log::error('CSV Upload Error: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
