<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Barangay;
use App\Models\City;
use App\Models\Household;
use App\Models\Member;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * HouseholdAdminController
 * 
 * Manages household CRUD operations via admin dashboard
 * 
 * FEATURES:
 * - List all households (with filters)
 * - Create new household
 * - View household details with members
 * - Update household info
 * - Delete household (only if user is 'head' role)
 * - Upload CSV (placeholder)
 * 
 * RBAC:
 * - Barangay Head (head): Can create, read, update, delete
 * - Encoder (encoder): Can create, read, update (no delete)
 * 
 * NOTES FOR STUDENT:
 * - CSV upload integration will be handled by HouseholdCsvImportService
 * - Real address data comes from locations hierarchy
 * - Household relationships: hasMany(Member), hasOne(User), belongsTo(Address)
 */
class HouseholdAdminController extends Controller
{
    /**
     * List households with pagination and filters
     * 
     * SEARCH FILTERS:
     * - household_name or household_code (text search)
     * - sitio/purok (location filter)
     * - barangay (location filter)
     */
    public function index(Request $request)
    {
        $query = Household::with(['address.barangay.city.province.region', 'members', 'user']);
        
        // Text search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('household_code', 'like', "%{$search}%")
                  ->orWhere('household_name', 'like', "%{$search}%");
            });
        }
        
        // Location filters
        if ($request->filled('purok_sitio')) {
            $query->whereHas('address', function ($q) use ($request) {
                $q->where('purok_sitio', 'like', '%' . $request->purok_sitio . '%');
            });
        }
        
        if ($request->filled('barangay_id')) {
            $query->whereHas('address', function ($q) use ($request) {
                $q->where('barangay_id', $request->barangay_id);
            });
        }
        
        $households = $query->latest()->paginate(15)->withQueryString();
        
        // Get barangays for filter dropdown
        $barangays = Barangay::all();
        
        return view('admin.households.index', [
            'households' => $households,
            'barangays' => $barangays,
            'filters' => $request->only(['search', 'purok_sitio', 'barangay_id']),
        ]);
    }

    /**
     * Show form to create new household
     */
    public function create()
    {
        $regions = Region::all();
        
        return view('admin.households.create', [
            'regions' => $regions,
        ]);
    }

    /**
     * Store new household in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'household_code' => 'required|string|unique:households|max:50',
            'household_name' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'emergency_contact' => 'nullable|string|max:255',
            
            // Address fields
            'region_id' => 'nullable|uuid|exists:regions,id',
            'province_id' => 'nullable|uuid|exists:provinces,id',
            'city_id' => 'nullable|uuid|exists:cities,id',
            'barangay_id' => 'nullable|uuid|exists:barangays,id',
            'purok_sitio' => 'nullable|string|max:255',
            'street_address' => 'nullable|string|max:255',
        ]);

        try {
            // Create address if location data provided
            $address = null;
            if (!empty($validated['barangay_id'])) {
                $address = Address::create([
                    'region_id' => $validated['region_id'] ?? null,
                    'province_id' => $validated['province_id'] ?? null,
                    'city_id' => $validated['city_id'] ?? null,
                    'barangay_id' => $validated['barangay_id'],
                    'purok_sitio' => $validated['purok_sitio'] ?? null,
                    'street_address' => $validated['street_address'] ?? null,
                ]);
            }

            // Create household
            $household = Household::create([
                'household_code' => $validated['household_code'],
                'household_name' => $validated['household_name'] ?? $validated['household_code'],
                'contact_number' => $validated['contact_number'] ?? null,
                'email' => $validated['email'] ?? null,
                'emergency_contact' => $validated['emergency_contact'] ?? null,
                'address_id' => $address?->id,
                'created_by' => auth()->id(),
            ]);

            return redirect()->route('admin.households.show', $household)
                ->with('success', "Household '{$household->household_code}' created successfully!");
        } catch (\Exception $e) {
            report($e);
            return back()->withInput()
                ->with('error', 'Failed to create household. ' . $e->getMessage());
        }
    }

    /**
     * Show household details with members
     */
    public function show(Household $household)
    {
        $household->load(['address.barangay.city.province.region', 'members', 'user']);
        
        return view('admin.households.show', [
            'household' => $household,
        ]);
    }

    /**
     * Show edit form for household
     */
    public function edit(Household $household)
    {
        $household->load('address');
        $regions = Region::all();
        
        return view('admin.households.edit', [
            'household' => $household,
            'regions' => $regions,
        ]);
    }

    /**
     * Update household in database
     */
    public function update(Request $request, Household $household)
    {
        $validated = $request->validate([
            'household_name' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'emergency_contact' => 'nullable|string|max:255',
            
            // Address fields
            'region_id' => 'nullable|uuid|exists:regions,id',
            'province_id' => 'nullable|uuid|exists:provinces,id',
            'city_id' => 'nullable|uuid|exists:cities,id',
            'barangay_id' => 'nullable|uuid|exists:barangays,id',
            'purok_sitio' => 'nullable|string|max:255',
            'street_address' => 'nullable|string|max:255',
        ]);

        try {
            // Update household
            $household->update([
                'household_name' => $validated['household_name'] ?? $household->household_name,
                'contact_number' => $validated['contact_number'] ?? null,
                'email' => $validated['email'] ?? null,
                'emergency_contact' => $validated['emergency_contact'] ?? null,
            ]);

            // Update or create address
            if (!empty($validated['barangay_id'])) {
                if ($household->address) {
                    $household->address->update([
                        'region_id' => $validated['region_id'] ?? null,
                        'province_id' => $validated['province_id'] ?? null,
                        'city_id' => $validated['city_id'] ?? null,
                        'barangay_id' => $validated['barangay_id'],
                        'purok_sitio' => $validated['purok_sitio'] ?? null,
                        'street_address' => $validated['street_address'] ?? null,
                    ]);
                } else {
                    $address = Address::create([
                        'region_id' => $validated['region_id'] ?? null,
                        'province_id' => $validated['province_id'] ?? null,
                        'city_id' => $validated['city_id'] ?? null,
                        'barangay_id' => $validated['barangay_id'],
                        'purok_sitio' => $validated['purok_sitio'] ?? null,
                        'street_address' => $validated['street_address'] ?? null,
                    ]);
                    $household->update(['address_id' => $address->id]);
                }
            }

            return redirect()->route('admin.households.show', $household)
                ->with('success', 'Household updated successfully!');
        } catch (\Exception $e) {
            report($e);
            return back()->withInput()
                ->with('error', 'Failed to update household. ' . $e->getMessage());
        }
    }

    /**
     * Delete household (only for 'head' role)
     * 
     * RBAC CHECK:
     * - Only users with 'head' role can delete
     * - Encoder role cannot delete (see route middleware)
     */
    public function destroy(Household $household)
    {
        try {
            $code = $household->household_code;
            $household->delete();
            
            return redirect()->route('admin.households.index')
                ->with('success', "Household '{$code}' deleted successfully!");
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to delete household. ' . $e->getMessage());
        }
    }

    /**
     * Handle CSV upload (placeholder for integration)
     * 
     * TODO FOR STUDENT:
     * - Implement CSV parsing
     * - Validate CSV format
     * - Use HouseholdCsvImportService for bulk import
     * - Handle duplicate detection
     * - Store import logs
     */
    public function uploadCsv(Request $request, Household $household)
    {
        return back()->with('info', 'CSV upload feature is being integrated. Please use the manual form for now.');
    }
}
