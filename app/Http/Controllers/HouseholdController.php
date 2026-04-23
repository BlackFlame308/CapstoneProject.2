<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Address;
use App\Models\User;
use App\Models\Member;
use App\Models\Role;
use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HouseholdController extends Controller
{
    public function index()
    {
        abort_if(! auth()->user()->can('view_households'), 403);

        try {
            $households = Household::with([
                'address.barangay.city.province.region',
                'members'
            ])->paginate(10);

            return view('households.index', compact('households'));

        } catch (\Exception $e) {
            \Log::error('Error fetching households: ' . $e->getMessage());
            return back()->with('error', 'Failed to fetch households');
        }
    }

    public function create(Request $request)
    {
        abort_if(! auth()->user()->can('manage_households'), 403);

        try {
            $regions = Region::all();
            $selectedRegion = $request->region_id;
            $selectedProvince = $request->province_id;
            $selectedCity = $request->city_id;
            $selectedBarangay = $request->barangay_id;

            $provinces = $selectedRegion ? Province::where('region_id', $selectedRegion)->get() : collect();
            $cities = $selectedProvince ? City::where('province_id', $selectedProvince)->get() : collect();
            $barangays = $selectedCity ? Barangay::where('city_id', $selectedCity)->get() : collect();

            return view('households.create', compact(
                'regions', 'provinces', 'cities', 'barangays',
                'selectedRegion', 'selectedProvince', 'selectedCity', 'selectedBarangay'
            ));

        } catch (\Exception $e) {
            \Log::error('Error loading create form: ' . $e->getMessage());
            return back()->with('error', 'Failed to load form');
        }
    }

    public function store(Request $request)
    {
        abort_if(! auth()->user()->isAdmin() && ! auth()->user()->isCaptain(), 403);

        try {
            $validated = $request->validate([
                'street' => 'nullable|string|max:255',
                'purok' => 'nullable|string|max:100',
                'barangay_id' => 'required|exists:barangays,id',
                'contact_number' => 'nullable|string|max:20',
                'emergency_contact' => 'nullable|string|max:20',
                'head_first_name' => 'required|string|max:100',
                'head_middle_name' => 'nullable|string|max:100',
                'head_last_name' => 'required|string|max:100',
            ]);

            return DB::transaction(function () use ($validated, $request) {
                $role = Role::where('name', 'Household')->first();
                if (!$role) {
                    throw new \Exception('Household role not found - run php artisan db:seed --class=RoleSeeder');
                }

                $address = Address::create([
                    'street' => $validated['street'],
                    'purok' => $validated['purok'],
                    'barangay_id' => $validated['barangay_id'],
                ]);

                $householdCode = 'HH-' . strtoupper(Str::random(8));
                $household = Household::create([
                    'household_code' => $householdCode,
                    'address_id' => $address->id,
                    'contact_number' => $validated['contact_number'] ?? null,
                    'emergency_contact' => $validated['emergency_contact'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                $tempPassword = Str::password(12);
                User::create([
                    'name' => trim($validated['head_first_name'] . ' ' . ($validated['head_middle_name'] ?? '') . ' ' . $validated['head_last_name']),
                    'email' => $householdCode . '@households.capstone.local',
                    'password' => bcrypt($tempPassword),
                    'role_id' => $role->id,
                    'household_id' => $household->id,
                    'must_change_password' => true,
                    'temp_password' => $tempPassword,
                ]);

                if ($request->filled('members') && is_array($request->members)) {
                    foreach ($request->members as $member) {
                        if (!empty($member['first_name']) && !empty($member['last_name']) && !empty($member['birth_date'])) {
                            Member::create([
                                'household_id' => $household->id,
                                'first_name' => $member['first_name'],
                                'middle_name' => $member['middle_name'] ?? null,
                                'last_name' => $member['last_name'],
                                'birth_date' => $member['birth_date'],
                                'sex' => $member['sex'],
                                'civil_status' => $member['civil_status'] ?? null,
                                'education_level' => $member['education_level'] ?? null,
                                'profession' => $member['profession'] ?? null,
                                'is_pwd' => $member['is_pwd'] ?? false,
                            ]);
                        }
                    }
                }

                return redirect()->route('households.index')
                    ->with('success', "Household '{$householdCode}' created successfully. Login: {$householdCode}@households.capstone.local");
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Household creation error: ' . $e->getMessage());
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(string $id)
    {
        $household = Household::with([
            'address.barangay.city.province.region',
            'members'
        ])->findOrFail($id);

        return view('households.show', compact('household'));
    }

    public function edit(string $id)
    {
        $household = Household::findOrFail($id);
        return view('households.edit', compact('household'));
    }

    public function update(Request $request, string $id)
    {
        $household = Household::findOrFail($id);
        $household->update($request->validated());
        return back()->with('success', 'Updated successfully');
    }

    public function destroy(string $id)
    {
        $household = Household::findOrFail($id);
        $household->members()->delete();
        $household->user()->delete();
        $household->address()->delete();
        $household->delete();
        return back()->with('success', 'Deleted successfully');
    }
}

