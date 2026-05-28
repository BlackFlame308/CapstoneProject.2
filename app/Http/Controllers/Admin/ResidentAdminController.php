<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Household;
use App\Models\Member;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * ResidentAdminController
 * 
 * Manages household members/residents
 * 
 * FEATURES:
 * - Add member to household
 * - Edit member details
 * - Delete member
 * - Calculate age from birth date
 * - Track demographics (PWD, pregnant, special needs)
 * 
 * RELATIONSHIPS:
 * - Member belongsTo Household
 * - Household hasMany Members
 * 
 * RBAC:
 * - Only encoder and head can manage members
 */
class ResidentAdminController extends Controller
{
    /**
     * Display all residents
     */
    public function index()
    {
        $residents = Member::with('household')->paginate(20);

        return view('admin.residents.index', [
            'residents' => $residents,
        ]);
    }

    /**
     * Show form to create new resident
     */
    public function create(Household $household)
    {
        return view('admin.residents.create', [
            'household' => $household,
        ]);
    }

    /**
     * Store new resident in database
     */
    public function store(Request $request, Household $household)
    {
        $validated = $request->validate([
            'first_name'      => 'required|string|max:100',
            'middle_name'     => 'nullable|string|max:100',
            'last_name'       => 'required|string|max:100',
            'birth_date'      => 'nullable|date|before:today',
            'sex'             => 'required|in:M,F',
            'relation'        => 'required|in:Head,Spouse,Child,Parent,Sibling,Grandchild,Others',
            'civil_status'    => 'required|in:Single,Married,Widowed,Separated',
            'education_level' => 'nullable|in:Elementary,High School,College,Post Graduate',
            'occupation'      => 'nullable|string|max:100',
            'is_pwd'          => 'boolean',
            'is_pregnant'     => 'boolean',
            'special_needs'   => 'nullable|string|max:255',
        ]);

        try {
            // Compute gender from sex (M = Male, F = Female)
            $gender = $validated['sex'] === 'F' ? 'Female' : 'Male';

            // Calculate age from birth date
            $age = null;
            $isSenior = false;
            if (!empty($validated['birth_date'])) {
                $age = Carbon::parse($validated['birth_date'])->age;
                $isSenior = $age >= 60;
            }

            $member = Member::create([
                'household_id'    => $household->household_id,
                'first_name'      => $validated['first_name'],
                'middle_name'     => $validated['middle_name'] ?? null,
                'last_name'       => $validated['last_name'],
                'name'            => $validated['first_name'] . ' ' . $validated['last_name'],
                'birth_date'      => $validated['birth_date'] ?? null,
                'age'             => $age,
                'sex'             => $validated['sex'],
                'gender'          => $gender,
                'relation'        => $validated['relation'],
                'civil_status'    => $validated['civil_status'],
                'education_level' => $validated['education_level'] ?? null,
                'occupation'      => $validated['occupation'] ?? null,
                'is_pwd'          => $request->boolean('is_pwd'),
                'is_pregnant'     => $request->boolean('is_pregnant'),
                'is_senior'       => $isSenior,
                'special_needs'   => $validated['special_needs'] ?? null,
            ]);

            // Update household member count
            $household->update(['member_count' => $household->members()->count()]);

            return redirect()->route('admin.households.show', $household)
                ->with('success', "Resident '{$member->name}' added successfully!");
        } catch (\Exception $e) {
            \Log::error('Resident store error: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to add resident. ' . $e->getMessage());
        }
    }

    /**
     * Show edit form for resident
     */
    public function edit(Member $member)
    {
        return view('admin.residents.edit', [
            'member' => $member,
            'household' => $member->household,
        ]);
    }

    /**
     * Update resident in database
     */
    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'first_name'      => 'required|string|max:100',
            'middle_name'     => 'nullable|string|max:100',
            'last_name'       => 'required|string|max:100',
            'birth_date'      => 'nullable|date|before:today',
            'sex'             => 'required|in:M,F',
            'relation'        => 'required|in:Head,Spouse,Child,Parent,Sibling,Grandchild,Others',
            'civil_status'    => 'required|in:Single,Married,Widowed,Separated',
            'education_level' => 'nullable|in:Elementary,High School,College,Post Graduate',
            'occupation'      => 'nullable|string|max:100',
            'is_pwd'          => 'boolean',
            'is_pregnant'     => 'boolean',
            'special_needs'   => 'nullable|string|max:255',
        ]);

        try {
            // Compute gender from sex (M = Male, F = Female)
            $gender = $validated['sex'] === 'F' ? 'Female' : 'Male';

            // Calculate age from birth date
            $age = null;
            $isSenior = false;
            if (!empty($validated['birth_date'])) {
                $age = Carbon::parse($validated['birth_date'])->age;
                $isSenior = $age >= 60;
            }

            $member->update([
                'first_name'      => $validated['first_name'],
                'middle_name'     => $validated['middle_name'] ?? null,
                'last_name'       => $validated['last_name'],
                'name'            => $validated['first_name'] . ' ' . $validated['last_name'],
                'birth_date'      => $validated['birth_date'] ?? null,
                'age'             => $age,
                'sex'             => $validated['sex'],
                'gender'          => $gender,
                'relation'        => $validated['relation'],
                'civil_status'    => $validated['civil_status'],
                'education_level' => $validated['education_level'] ?? null,
                'occupation'      => $validated['occupation'] ?? null,
                'is_pwd'          => $request->boolean('is_pwd'),
                'is_pregnant'     => $request->boolean('is_pregnant'),
                'is_senior'       => $isSenior,
                'special_needs'   => $validated['special_needs'] ?? null,
            ]);

            return redirect()->route('admin.households.show', $member->household)
                ->with('success', "Resident '{$member->name}' updated successfully!");
        } catch (\Exception $e) {
            \Log::error('Resident update error: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to update resident. ' . $e->getMessage());
        }
    }

    /**
     * Delete resident from household
     * 
     * RBAC:
     * - Only head role can delete
     */
    public function destroy(Member $member)
    {
        try {
            $household = $member->household;
            $name = $member->name;
            $member->delete();

            // Update household member count
            $household->update(['member_count' => $household->members()->count()]);

            return redirect()->route('admin.households.show', $household)
                ->with('success', "Resident '{$name}' deleted successfully!");
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to delete resident. ' . $e->getMessage());
        }
    }
}
