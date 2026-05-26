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

class HouseholdController extends Controller
{
    public function __construct(
        private readonly HouseholdService $householdService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('view', Household::class);

        return redirect()->route('admin.households.index', $request->query());
    }

    public function create(Request $request)
    {
        $this->authorize('create', Household::class);

        return redirect()->route('admin.households.create');
    }

    public function store(StoreHouseholdRequest $request)
    {
        $validated = $request->validated();

        try {
            $household = $this->householdService->create(
                $validated,
                auth()->id()
            );

            return redirect()->route('admin.households.index')
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
            'user',
        ])->findOrFail($id);

        return redirect()->route('admin.households.show', $household);
    }

    public function edit(string $id)
    {
        $household = Household::with(['address.barangay.city.province.region', 'members'])->findOrFail($id);
        $this->authorize('update', $household);

        return redirect()->route('admin.households.edit', $household);
    }

    public function update(UpdateHouseholdRequest $request, string $id)
    {
        $validated = $request->validated();

        try {
            $household = $this->householdService->update(
                Household::with(['address', 'members'])->findOrFail($id),
                $validated
            );

            return redirect()->route('admin.households.show', $household)
                ->with('success', 'Household updated successfully.');
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to update household.')->withInput();
        }
    }

public function destroy(string $id)
    {
        try {
            // First check if household exists
            $household = Household::with('address')
                ->findOrFail($id);

            // Check authorization
            $this->authorize('delete', $household);

            // Delete the household
            $this->householdService->delete($household);

            // Redirect to index with success message
            return redirect()->route('admin.households.index')
                ->with('success', 'Household deleted successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Household not found - redirect with error
            return redirect()->route('admin.households.index')
                ->with('error', 'Household not found.');
        } catch (\Exception $e) {
            // Log the error for debugging
            report($e);

            return redirect()->route('admin.households.index')
                ->with('error', 'Failed to delete household: ' . $e->getMessage());
        }
    }
}
