<?php

namespace App\Http\Controllers\Household;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Eager-load the household along with its address and members
        $household = $user->household()->with(['address.barangay.city.province.region', 'members'])->first();

        if (!$household) {
            abort(403, 'Your account is not assigned to any household. Please contact your Barangay Captain.');
        }

        // Calculate basic demographics analytics for the household
        $totalMembers = $household->members->count();
        $pwdCount = $household->members->where('is_pwd', true)->count();
        $pregnantCount = $household->members->where('is_pregnant', true)->count();
        
        $childrenCount = $household->members->filter(function($member) {
            return $member->age < 18;
        })->count();

        $seniorsCount = $household->members->filter(function($member) {
            return $member->age >= 60;
        })->count();

        $adultsCount = $household->members->filter(function($member) {
            return $member->age >= 18 && $member->age < 60;
        })->count();

        // Subsystem reports (read-only empty collection for placeholder view)
        $reports = collect([]);

        return view('household.dashboard', [
            'household' => $household,
            'totalMembers' => $totalMembers,
            'pwdCount' => $pwdCount,
            'pregnantCount' => $pregnantCount,
            'childrenCount' => $childrenCount,
            'seniorsCount' => $seniorsCount,
            'adultsCount' => $adultsCount,
            'reports' => $reports,
        ]);
    }
}
