<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHouseholdRequest;
use App\Http\Requests\UpdateHouseholdRequest;
use App\Models\Household;
use App\Models\Region;
use App\Models\Member;
use App\Models\Role;
use App\Models\User;
use App\Services\HouseholdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class HouseholdController extends Controller
{
    public function __construct(
        private readonly HouseholdService $householdService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('view', Household::class);

        $query = Household::with([
            'address.barangay.city.province.region',
            'members',
        ]);

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

if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('household_code', 'like', "%{$search}%")
                      ->orWhere('household_name', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQ) use ($search) {
                          $userQ->where('name', 'like', "%{$search}%");
                      });
                });
            }

        $households = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Household/Index', [
            'households' => $households,
            'filters'    => $request->only(['search', 'purok_sitio', 'barangay_id']),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Household::class);

        return Inertia::render('Household/Create');
    }

    public function store(StoreHouseholdRequest $request)
    {
        $validated = $request->validated();

        try {
            $household = $this->householdService->create(
                $validated,
                auth()->id()
            );

            return redirect()->route('households.index')
                ->with('success', "Household '{$household->household_code}' created successfully.");
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to create household. Please try again.')->withInput();
        }
    }

    public function show(string $id)
    {
        $this->authorize('view', Household::class);

        $household = Household::with([
            'address.barangay.city.province.region',
            'members',
        ])->findOrFail($id);

        return Inertia::render('Household/Show', [
            'household' => $household,
        ]);
    }

    public function edit(string $id)
    {
        $this->authorize('update', Household::class);

        $household = Household::with(['address.barangay.city.province.region'])->findOrFail($id);

        return Inertia::render('Household/Edit', [
            'household' => $household,
        ]);
    }

    public function update(UpdateHouseholdRequest $request, string $id)
    {
        $validated = $request->validated();

        try {
            $household = $this->householdService->update(
                Household::with(['address', 'members'])->findOrFail($id),
                $validated
            );

            return redirect()->route('households.show', $household)
                ->with('success', 'Household updated successfully.');
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to update household.')->withInput();
        }
    }

    public function destroy(string $id)
    {
        $this->authorize('delete', Household::class);

        try {
            $household = Household::with('address')->findOrFail($id);
            $this->householdService->delete($household);

            return back()->with('success', 'Household deleted successfully.');
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to delete household.');
        }
    }
}

