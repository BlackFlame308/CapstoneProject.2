<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Barangay;
use App\Models\City;
use App\Models\Household;
use App\Models\Region;
use App\Services\HouseholdAccountService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * HouseholdAdminController
 *
 * Manages household CRUD operations via the admin dashboard.
 * CSV upload is handled separately via CSVImportDashboardController.
 */
class HouseholdAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Household::with(['address.barangay.city.province.region', 'members', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q
                ->where('household_code', 'like', "%{$search}%")
                ->orWhere('household_name', 'like', "%{$search}%")
            );
        }

        if ($request->filled('purok_sitio')) {
            $query->whereHas('address', fn($q) =>
                $q->where('purok_sitio', 'like', '%' . $request->purok_sitio . '%')
            );
        }

        $locationFilter = $request->input('location');
        $barangayId = $request->input('barangay_id');
        $cityId = $request->input('city_id');

        if (!empty($locationFilter)) {
            if (str_starts_with($locationFilter, 'city:')) {
                $cityId = (int) substr($locationFilter, 5);
                $query->whereHas('address.barangay', fn($q) => $q->where('city_id', $cityId));
            } elseif (str_starts_with($locationFilter, 'barangay:')) {
                $barangayId = (int) substr($locationFilter, 9);
                $query->whereHas('address', fn($q) => $q->where('barangay_id', $barangayId));
            } elseif (is_numeric($locationFilter)) {
                $barangayId = (int) $locationFilter;
                $query->whereHas('address', fn($q) => $q->where('barangay_id', $barangayId));
            } elseif (stripos($locationFilter, 'carcar') !== false || stripos($locationFilter, 'car-car') !== false) {
                $query->whereHas('address.barangay.city', fn($q) => $q->where('city_name', 'like', '%Carcar%'));
            }
        } elseif (!empty($barangayId)) {
            $query->whereHas('address', fn($q) => $q->where('barangay_id', $barangayId));
        } elseif (!empty($cityId)) {
            $query->whereHas('address.barangay', fn($q) => $q->where('city_id', $cityId));
        }

        return view('admin.households.index', [
            'households' => $query->orderBy('household_name', 'asc')->paginate(15)->withQueryString(),
            'cities'     => City::orderBy('city_name')->get(),
            'barangays'  => Barangay::with('city')->orderBy('barangay_name')->get(),
            'filters'    => [
                'search'      => $request->input('search'),
                'purok_sitio' => $request->input('purok_sitio'),
                'location'    => $locationFilter,
                'barangay_id' => $barangayId,
                'city_id'     => $cityId,
            ],
        ]);
    }

    public function create()
    {
        return view('admin.households.create', ['regions' => Region::orderBy('name')->get()]);
    }

    public function store(Request $request, HouseholdAccountService $accountService)
    {
        $validated = $request->validate([
            'household_code'    => ['required', 'string', 'max:50', Rule::unique('households', 'household_code')->whereNull('deleted_at')],
            'household_name'    => 'nullable|string|max:255',
            'contact_number'    => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'emergency_contact' => 'nullable|string|max:255',
            'region_id'         => 'nullable|integer|exists:regions,region_id',
            'province_id'       => 'nullable|integer|exists:provinces,province_id',
            'city_id'           => 'nullable|integer|exists:cities,city_id',
            'barangay_id'       => 'nullable|integer|exists:barangays,barangay_id',
            'purok_sitio'       => 'nullable|string|max:255',
            'street_address'    => 'nullable|string|max:255',
        ]);

        try {
            $address   = $this->createAddressIfProvided($validated);
            $household = Household::create([
                'household_code'    => $validated['household_code'],
                'household_name'    => $validated['household_name'] ?? $validated['household_code'],
                'contact_number'    => $validated['contact_number'] ?? null,
                'email'             => $validated['email'] ?? null,
                'emergency_contact' => $validated['emergency_contact'] ?? null,
                'address_id'        => $address?->address_id,
                'created_by'        => auth()->id(),
            ]);

            $accountResult = $accountService->provision($household, $validated['email'] ?? null);

            $redirect = redirect()->route('admin.households.index')
                ->with('success', "Household '{$household->household_code}' created successfully.");

            if ($accountResult) {
                $redirect->with('new_account', [
                    'username' => $accountResult['user']->username,
                    'email'    => $accountResult['user']->email,
                    'password' => $accountResult['password'],
                ]);
            }

            return $redirect;
        } catch (\Exception $e) {
            \Log::error('Household store error: ' . $e->getMessage());
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Household $household)
    {
        $household->load(['address.barangay.city.province.region', 'members', 'user']);
        return view('admin.households.show', compact('household'));
    }

    public function edit(Household $household)
    {
        $household->load('address');
        return view('admin.households.edit', [
            'household' => $household,
            'regions'   => Region::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Household $household)
    {
        $validated = $request->validate([
            'household_name'    => 'nullable|string|max:255',
            'contact_number'    => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'emergency_contact' => 'nullable|string|max:255',
            'region_id'         => 'nullable|integer|exists:regions,region_id',
            'province_id'       => 'nullable|integer|exists:provinces,province_id',
            'city_id'           => 'nullable|integer|exists:cities,city_id',
            'barangay_id'       => 'nullable|integer|exists:barangays,barangay_id',
            'purok_sitio'       => 'nullable|string|max:255',
            'street_address'    => 'nullable|string|max:255',
        ]);

        try {
            $household->update([
                'household_name'    => $validated['household_name'] ?? $household->household_name,
                'contact_number'    => $validated['contact_number'] ?? null,
                'email'             => $validated['email'] ?? null,
                'emergency_contact' => $validated['emergency_contact'] ?? null,
            ]);

            $this->syncAddress($household, $validated);

            return redirect()->route('admin.households.show', $household)
                ->with('success', 'Household updated successfully!');
        } catch (\Exception $e) {
            report($e);
            return back()->withInput()->with('error', 'Failed to update household. ' . $e->getMessage());
        }
    }

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

    public function uploadCsv(Request $request, Household $household)
    {
        return back()->with('info', 'CSV upload feature is being integrated. Please use the manual form for now.');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function resolveDefaultBarangay(Request $request): ?string
    {
        return $request->input('barangay_id') ?: null;
    }

    private function createAddressIfProvided(array $validated): ?Address
    {
        $hasAddressData = !empty($validated['barangay_id']) ||
                          !empty($validated['purok_sitio']) ||
                          !empty($validated['street_address']);

        if (!$hasAddressData) return null;

        return Address::create([
            'barangay_id' => $validated['barangay_id'] ?? null,
            'purok_sitio' => $validated['purok_sitio'] ?? null,
            'street'      => $validated['street_address'] ?? null,
        ]);
    }

    private function syncAddress(Household $household, array $validated): void
    {
        $hasAddressData = !empty($validated['barangay_id']) ||
                          !empty($validated['purok_sitio']) ||
                          !empty($validated['street_address']);

        if (!$hasAddressData) return;

        if ($household->address) {
            $household->address->update([
                'barangay_id' => $validated['barangay_id'] ?? $household->address->barangay_id,
                'purok_sitio' => $validated['purok_sitio'] ?? null,
                'street'      => $validated['street_address'] ?? null,
            ]);
        } else {
            $address = Address::create([
                'barangay_id' => $validated['barangay_id'] ?? null,
                'purok_sitio' => $validated['purok_sitio'] ?? null,
                'street'      => $validated['street_address'] ?? null,
            ]);
            $household->update(['address_id' => $address->address_id]);
        }
    }
}
