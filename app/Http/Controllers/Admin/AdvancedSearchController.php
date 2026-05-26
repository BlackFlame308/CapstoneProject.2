<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Household;
use App\Models\Member;
use Illuminate\Http\Request;

class AdvancedSearchController extends Controller
{
    /**
     * Show advanced search form
     */
    public function form()
    {
        return view('admin.search.advanced-search');
    }

    /**
     * Execute advanced search across households and members
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $searchType = $request->get('type', 'all'); // all, households, members
        $filters = [];

        $results = [
            'households' => [],
            'members' => [],
            'query' => $query,
            'searchType' => $searchType,
        ];

        // Household search
        if ($searchType === 'all' || $searchType === 'households') {
            $householdQuery = Household::query();

            if ($query) {
                $householdQuery->where(function($q) use ($query) {
                    $q->where('household_code', 'like', "%{$query}%")
                      ->orWhere('household_name', 'like', "%{$query}%")
                      ->orWhere('contact_number', 'like', "%{$query}%");
                });
            }

            // Apply filters
            if ($request->filled('barangay_id')) {
                $householdQuery->whereHas('address', function($q) use ($request) {
                    $q->where('barangay_id', $request->barangay_id);
                });
            }

            if ($request->filled('contact_number')) {
                $householdQuery->where('contact_number', 'like', "%{$request->contact_number}%");
            }

            $results['households'] = $householdQuery->limit(20)->get()->map(function($h) {
                return [
                    'id' => $h->household_id,
                    'code' => $h->household_code,
                    'name' => $h->household_name,
                    'contact' => $h->contact_number,
                    'type' => 'household',
                    'url' => route('admin.households.show', $h),
                ];
            })->toArray();
        }

        // Member search
        if ($searchType === 'all' || $searchType === 'members') {
            $memberQuery = Member::query();

            if ($query) {
                $memberQuery->where(function($q) use ($query) {
                    $q->where('first_name', 'like', "%{$query}%")
                      ->orWhere('middle_name', 'like', "%{$query}%")
                      ->orWhere('last_name', 'like', "%{$query}%");
                });
            }

            // Apply filters
            if ($request->filled('gender_id')) {
                $memberQuery->where('gender_id', $request->gender_id);
            }

            if ($request->filled('vulnerable_only')) {
                $memberQuery->whereHas('vulnerableGroups');
            }

            $results['members'] = $memberQuery->limit(20)->get()->map(function($m) {
                return [
                    'id' => $m->member_id,
                    'name' => "{$m->first_name} {$m->last_name}",
                    'age' => $m->birth_date ? \Carbon\Carbon::parse($m->birth_date)->age : 'N/A',
                    'household' => $m->household->household_code,
                    'type' => 'member',
                    'url' => route('admin.residents.edit', $m),
                ];
            })->toArray();
        }

        return view('admin.search.results', $results);
    }
}
