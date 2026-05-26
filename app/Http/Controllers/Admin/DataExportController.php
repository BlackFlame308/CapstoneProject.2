<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Household;
use App\Models\Member;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class DataExportController extends Controller
{
    /**
     * Export households as Excel
     */
    public function exportHouseholdsExcel()
    {
        return Excel::download(new HouseholdsExport(), 'households_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    /**
     * Export households as PDF
     */
    public function exportHouseholdsPDF()
    {
        $households = Household::with('address')->get();

        $pdf = PDF::loadView('admin.exports.households-pdf', [
            'households' => $households,
            'exportDate' => now(),
        ]);

        return $pdf->download('households_' . now()->format('Y-m-d_His') . '.pdf');
    }

    /**
     * Export members as Excel
     */
    public function exportMembersExcel()
    {
        return Excel::download(new MembersExport(), 'members_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    /**
     * Export members as PDF
     */
    public function exportMembersPDF()
    {
        $members = Member::with('household')->get();

        $pdf = PDF::loadView('admin.exports.members-pdf', [
            'members' => $members,
            'exportDate' => now(),
        ]);

        return $pdf->download('members_' . now()->format('Y-m-d_His') . '.pdf');
    }

    /**
     * Export analytics report
     */
    public function exportAnalyticsReport()
    {
        $totalHouseholds = Household::count();
        $totalMembers = Member::count();
        $vulnerableGroups = Member::with('vulnerableGroups')->get();

        $pdf = PDF::loadView('admin.exports.analytics-report', [
            'totalHouseholds' => $totalHouseholds,
            'totalMembers' => $totalMembers,
            'exportDate' => now(),
        ]);

        return $pdf->download('analytics_report_' . now()->format('Y-m-d_His') . '.pdf');
    }
}
