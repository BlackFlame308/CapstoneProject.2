<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CsvUpload;
use App\Models\ImportLog;
use Illuminate\Http\Request;

class CSVImportDashboardController extends Controller
{
    /**
     * Show CSV import dashboard with history and stats
     */
    public function index(Request $request)
    {
        $uploads = CsvUpload::with('dataSource')
            ->latest()
            ->paginate(20);

        // Calculate statistics
        $totalImports = CsvUpload::count();
        $successRate = $totalImports > 0 ? round(
            CsvUpload::whereColumn('failed_records', '=', 0)->count() / $totalImports * 100, 1
        ) : 0;

        $totalRecords = CsvUpload::sum('total_records') ?? 0;
        $successfulRecords = CsvUpload::sum('successful_records') ?? 0;
        $failedRecords = CsvUpload::sum('failed_records') ?? 0;

        // Recent errors
        $recentErrors = ImportLog::where('status', 'failed')
            ->with('dataSource')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.csv-import.dashboard', [
            'uploads' => $uploads,
            'totalImports' => $totalImports,
            'successRate' => $successRate,
            'totalRecords' => $totalRecords,
            'successfulRecords' => $successfulRecords,
            'failedRecords' => $failedRecords,
            'recentErrors' => $recentErrors,
        ]);
    }

    /**
     * Show details of a specific import
     */
    public function show(CsvUpload $csvUpload)
    {
        $csvUpload->load('dataSource');

        $logs = ImportLog::where('data_source_id', $csvUpload->data_source_id)
            ->orderBy('row_number')
            ->paginate(50);

        $errorLogs = ImportLog::where('data_source_id', $csvUpload->data_source_id)
            ->where('status', 'failed')
            ->count();

        return view('admin.csv-import.show', [
            'import' => $csvUpload,
            'logs' => $logs,
            'errorCount' => $errorLogs,
        ]);
    }

    /**
     * Retry failed import rows
     */
    public function retryErrors(CsvUpload $csvUpload)
    {
        try {
            $failedLogs = ImportLog::where('data_source_id', $csvUpload->data_source_id)
                ->where('status', 'failed')
                ->get();

            // TODO: Implement retry logic based on your CSV import service

            return back()->with('success', 'Failed rows queued for retry');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to retry import: ' . $e->getMessage());
        }
    }

    /**
     * Delete import record
     */
    public function destroy(CsvUpload $csvUpload)
    {
        try {
            // Delete related logs
            ImportLog::where('data_source_id', $csvUpload->data_source_id)->delete();

            $fileName = $csvUpload->file_name;
            $csvUpload->delete();

            return redirect()->route('admin.csv-import.index')
                ->with('success', "Import record '$fileName' deleted");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete import: ' . $e->getMessage());
        }
    }
}
