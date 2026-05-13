<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VulnerableGroup;
use Illuminate\Http\Request;

class VulnerableGroupAdminController extends Controller
{
    /**
     * List all vulnerable groups
     */
    public function index(Request $request)
    {
        $query = VulnerableGroup::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('vulnerable_group_label', 'like', "%{$search}%")
                  ->orWhere('vulnerable_group_key', 'like', "%{$search}%");
            });
        }

        $groups = $query->latest()->paginate(20)->withQueryString();

        return view('admin.vulnerable-groups.index', [
            'groups' => $groups,
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.vulnerable-groups.create');
    }

    /**
     * Store new vulnerable group
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vulnerable_group_key' => 'required|string|unique:vulnerable_groups|max:20',
            'vulnerable_group_label' => 'required|string|max:20',
        ]);

        try {
            VulnerableGroup::create($validated);

            return redirect()->route('admin.vulnerable-groups.index')
                ->with('success', "Vulnerable group '{$validated['vulnerable_group_label']}' created successfully!");
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create vulnerable group. ' . $e->getMessage());
        }
    }

    /**
     * Show edit form
     */
    public function edit(VulnerableGroup $vulnerableGroup)
    {
        return view('admin.vulnerable-groups.edit', [
            'group' => $vulnerableGroup,
        ]);
    }

    /**
     * Update vulnerable group
     */
    public function update(Request $request, VulnerableGroup $vulnerableGroup)
    {
        $validated = $request->validate([
            'vulnerable_group_key' => 'required|string|max:20|unique:vulnerable_groups,vulnerable_group_key,' . $vulnerableGroup->vulnerable_group_id,
            'vulnerable_group_label' => 'required|string|max:20',
        ]);

        try {
            $vulnerableGroup->update($validated);

            return redirect()->route('admin.vulnerable-groups.index')
                ->with('success', "Vulnerable group updated successfully!");
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update vulnerable group. ' . $e->getMessage());
        }
    }

    /**
     * Delete vulnerable group
     */
    public function destroy(VulnerableGroup $vulnerableGroup)
    {
        try {
            $label = $vulnerableGroup->vulnerable_group_label;
            $vulnerableGroup->delete();

            return redirect()->route('admin.vulnerable-groups.index')
                ->with('success', "Vulnerable group '{$label}' deleted successfully!");
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to delete vulnerable group. ' . $e->getMessage());
        }
    }
}
