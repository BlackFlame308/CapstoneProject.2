<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Member;
use App\Models\Analytic;
use App\Models\Barangay;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display dashboard with analytics
     */
    public function index()
    {
        try {
            // Overall statistics
            $totalHouseholds = Household::count();
            $totalMembers = Member::count();
            $totalPWD = Member::where('is_pwd', true)->count();
            $totalSeniors = Member::whereRaw('YEAR(CURDATE()) - YEAR(birth_date) >= 60')->count();

            // Get barangay-wise breakdown
            $barangayStats = Barangay::withCount(['addresses' => function ($q) {
                $q->join('households', 'addresses.id', '=', 'households.address_id');
            }])
            ->with(['analytics'])
            ->get();

            // Recent households
            $recentHouseholds = Household::with(['address.barangay', 'members'])
                ->latest('created_at')
                ->take(5)
                ->get();

            // Chart data for members by barangay
            $membersByBarangay = DB::table('members')
                ->join('households', 'members.household_id', '=', 'households.id')
                ->join('addresses', 'households.address_id', '=', 'addresses.id')
                ->join('barangays', 'addresses.barangay_id', '=', 'barangays.id')
                ->groupBy('barangays.name')
                ->select('barangays.name', DB::raw('COUNT(members.id) as count'))
                ->get();

            // Age distribution for pie chart
            $childrenCount = Member::whereRaw('YEAR(CURDATE()) - YEAR(birth_date) < 18')->count();
            $adultsCount = Member::whereRaw('YEAR(CURDATE()) - YEAR(birth_date) BETWEEN 18 AND 59')->count();
            $seniorsCount = $totalSeniors;

            $totalUsers = User::count();
            $totalCaptains = User::whereHas('role', function ($query) {
                $query->where('name', 'Captain');
            })->count();

            return view('dashboard.index', compact(
                'totalHouseholds',
                'totalMembers',
                'totalPWD',
                'totalSeniors',
                'totalUsers',
                'totalCaptains',
                'barangayStats',
                'recentHouseholds',
                'membersByBarangay',
                'childrenCount',
                'adultsCount',
                'seniorsCount'
            ));
        } catch (\Exception $e) {
            \Log::error('Dashboard Index Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load dashboard. Please try again.');
        }
    }

    /**
     * Update analytics for all barangays
     */
    public function updateAnalytics()
    {
        try {
            $barangays = Barangay::all();

            if ($barangays->isEmpty()) {
                return redirect()->back()->with('warning', 'No barangays found to update analytics.');
            }

            $updatedCount = 0;

            foreach ($barangays as $barangay) {
                try {
                    $households = DB::table('households')
                        ->join('addresses', 'households.address_id', '=', 'addresses.id')
                        ->where('addresses.barangay_id', $barangay->id)
                        ->count();

                    $population = DB::table('members')
                        ->join('households', 'members.household_id', '=', 'households.id')
                        ->join('addresses', 'households.address_id', '=', 'addresses.id')
                        ->where('addresses.barangay_id', $barangay->id)
                        ->count();

                    $pwd = DB::table('members')
                        ->join('households', 'members.household_id', '=', 'households.id')
                        ->join('addresses', 'households.address_id', '=', 'addresses.id')
                        ->where('addresses.barangay_id', $barangay->id)
                        ->where('members.is_pwd', true)
                        ->count();

                    $seniors = DB::table('members')
                        ->join('households', 'members.household_id', '=', 'households.id')
                        ->join('addresses', 'households.address_id', '=', 'addresses.id')
                        ->where('addresses.barangay_id', $barangay->id)
                        ->whereRaw('YEAR(CURDATE()) - YEAR(members.birth_date) >= 60')
                        ->count();

                    Analytic::updateOrCreate(
                        ['barangay_id' => $barangay->id, 'sitio' => null],
                        [
                            'total_households' => $households,
                            'total_population' => $population,
                            'total_pwd' => $pwd,
                            'total_seniors' => $seniors,
                        ]
                    );

                    $updatedCount++;
                } catch (\Exception $e) {
                    \Log::error("Error updating analytics for barangay {$barangay->id}: " . $e->getMessage());
                    continue;
                }
            }

            return redirect()->back()->with('success', "Analytics updated successfully for $updatedCount barangays!");
        } catch (\Exception $e) {
            \Log::error('Update Analytics Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update analytics. Please try again.');
        }
    }
}
