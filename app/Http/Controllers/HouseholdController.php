<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Address;
use App\Models\User;
use App\Models\Member;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HouseholdController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort_if(! auth()->user()->can('view_households'), 403);

        try {
            $households = Household::with(['address.barangay.city.province.region', 'members'])->paginate(10);
            return view('households.index', compact('households'));
        } catch (\Exception $e) {
            \Log::error('Error fetching households: ' . $e->getMessage());
            return back()->with('error', 'Failed to fetch households');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(! auth()->user()->can('manage_households'), 403);

        try {
            return view('households.create');
        } catch (\Exception $e) {
            \Log::error('Error showing create form: ' . $e->getMessage());
            return back()->with('error', 'Failed to load form');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(! auth()->user()->can('manage_households'), 403);

        try {
            // Validate input
            $validated = $request->validate([
                'street' => 'nullable|string|max:255',
                'purok' => 'nullable|string|max:100',
                'barangay_id' => 'required|exists:barangays,id',
                'contact_number' => 'nullable|string|max:50',
                'emergency_contact' => 'nullable|string|max:50',
                'head_first_name' => 'required|string|max:100',
                'head_middle_name' => 'nullable|string|max:100',
                'head_last_name' => 'required|string|max:100',
                'members.*.first_name' => 'required|string|max:100',
                'members.*.last_name' => 'required|string|max:100',
                'members.*.birth_date' => 'required|date',
                'members.*.sex' => 'required|in:M,F',
                'members.*.civil_status' => 'nullable|string|max:50',
                'members.*.education_level' => 'nullable|string|max:100',
                'members.*.profession' => 'nullable|string|max:100',
                'members.*.is_pwd' => 'nullable|boolean',
            ], [
                'barangay_id.required' => 'Barangay is required',
                'barangay_id.exists' => 'Selected barangay does not exist',
                'head_first_name.required' => 'Head first name is required',
                'head_last_name.required' => 'Head last name is required',
                'members.*.first_name.required' => 'Member first name is required',
                'members.*.birth_date.required' => 'Member birth date is required',
                'members.*.birth_date.date' => 'Member birth date must be valid',
            ]);

            // Get household role
            $householdRole = Role::where('name', 'Household')->first() ?? Role::where('id', 3)->first();
            if (!$householdRole) {
                throw new \Exception('Household role not found in system');
            }

            // Create address
            $address = Address::create([
                'street' => $request->street ?: null,
                'purok' => $request->purok ?: null,
                'barangay_id' => $request->barangay_id,
            ]);

            // Generate household code
            $householdCode = 'HH-' . strtoupper(Str::random(8));

            // Create household
            $household = Household::create([
                'household_code' => $householdCode,
                'address_id' => $address->id,
                'contact_number' => $request->contact_number,
                'emergency_contact' => $request->emergency_contact,
                'created_by' => auth()->id(),
            ]);

            // Create user account for household
            $username = strtolower(str_replace(' ', '_', $request->head_first_name . '_' . $request->head_last_name));
            $tempPassword = 'Temp_' . Str::random(8);

            $user = User::create([
                'name' => $request->head_first_name . ' ' . $request->head_last_name,
                'email' => $username . '@household.local',
                'password' => bcrypt($tempPassword),
                'role_id' => $householdRole->id,
                'household_id' => $household->id,
                'must_change_password' => true,
            ]);

            // Create members
            if ($request->has('members') && is_array($request->members)) {
                foreach ($request->members as $memberData) {
                    Member::create([
                        'household_id' => $household->id,
                        'first_name' => $memberData['first_name'],
                        'middle_name' => $memberData['middle_name'] ?? null,
                        'last_name' => $memberData['last_name'],
                        'birth_date' => $memberData['birth_date'],
                        'sex' => $memberData['sex'],
                        'civil_status' => $memberData['civil_status'] ?? null,
                        'education_level' => $memberData['education_level'] ?? null,
                        'profession' => $memberData['profession'] ?? null,
                        'is_pwd' => $memberData['is_pwd'] ?? false,
                    ]);
                }
            }

            \Log::info("Household created: {$household->household_code} by user " . auth()->id());

            return redirect()->route('households.index')->with('success', 'Household created successfully. Temporary password: ' . $tempPassword);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Household creation validation failed: ' . json_encode($e->errors()));
            return redirect()->back()->withErrors($e->errors())->with('error', 'Validation failed');
        } catch (\Exception $e) {
            \Log::error('Error creating household: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create household: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort_if(! auth()->user()->can('view_households'), 403);

        try {
            $household = Household::with(['address.barangay', 'members'])->findOrFail($id);
            return view('households.show', compact('household'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("Household not found: {$id}");
            return back()->with('error', 'Household not found');
        } catch (\Exception $e) {
            \Log::error('Error showing household: ' . $e->getMessage());
            return back()->with('error', 'Failed to load household');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        abort_if(! auth()->user()->can('manage_households'), 403);

        try {
            $household = Household::findOrFail($id);
            return view('households.edit', compact('household'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("Household not found for editing: {$id}");
            return back()->with('error', 'Household not found');
        } catch (\Exception $e) {
            \Log::error('Error showing edit form: ' . $e->getMessage());
            return back()->with('error', 'Failed to load form');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        abort_if(! auth()->user()->can('manage_households'), 403);

        try {
            $household = Household::findOrFail($id);

            $validated = $request->validate([
                'street' => 'nullable|string|max:255',
                'purok' => 'nullable|string|max:100',
                'contact_number' => 'nullable|string|max:50',
                'emergency_contact' => 'nullable|string|max:50',
            ]);

            $household->address->update([
                'street' => $request->street,
                'purok' => $request->purok,
            ]);

            $household->update([
                'contact_number' => $request->contact_number,
                'emergency_contact' => $request->emergency_contact,
            ]);

            \Log::info("Household updated: {$household->household_code}");

            return redirect()->route('households.index')->with('success', 'Household updated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("Household not found for update: {$id}");
            return back()->with('error', 'Household not found');
        } catch (\Exception $e) {
            \Log::error('Error updating household: ' . $e->getMessage());
            return back()->with('error', 'Failed to update household');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        abort_if(! auth()->user()->can('manage_households'), 403);

        try {
            $household = Household::findOrFail($id);
            
            \Log::info("Deleting household: {$household->household_code}");

            // Delete related members
            $household->members()->delete();

            // Delete address
            $household->address()->delete();

            // Delete user accounts
            User::where('household_id', $household->id)->delete();

            // Delete household
            $household->delete();

            \Log::info("Household deleted: {$household->household_code}");

            return redirect()->route('households.index')->with('success', 'Household deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("Household not found for deletion: {$id}");
            return back()->with('error', 'Household not found');
        } catch (\Exception $e) {
            \Log::error('Error deleting household: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete household');
        }
    }
}

