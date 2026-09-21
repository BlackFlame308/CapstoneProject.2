<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * AccountAdminController
 *
 * Manages user accounts in the admin dashboard.
 * Only Captains can delete accounts; Encoders can create and edit.
 */
class AccountAdminController extends Controller
{
    /** Roles visible in create/edit dropdowns */
    private const MANAGEABLE_ROLES = ['Captain', 'Encoder', 'Moderator', 'personel', 'personnel', 'Household'];

    public function index(Request $request)
    {
        abort_unless(auth()->user()?->canManageAccounts(), 403, 'You are not authorized to manage accounts.');

        $query = User::with(['role', 'household']);

        if ($request->filled('role')) {
            $query->whereHas('role', fn($q) => $q->where('name', $request->role));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q
                ->where('name',     'like', "%{$search}%")
                ->orWhere('email',    'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
            );
        }

        return view('admin.accounts.index', [
            'users'   => $query->latest()->paginate(15)->withQueryString(),
            'roles'   => Role::where('name', '!=', 'Household')->orderBy('name')->get()->unique(fn($r) => strtolower($r->name))->values(),
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    public function create()
    {
        abort_unless(auth()->user()?->canManageAccounts(), 403, 'You are not authorized to create accounts.');

        return view('admin.accounts.create', [
            'households' => Household::orderBy('household_code')->get(),
            'roles'      => $this->manageableRoles(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'username'       => 'required|string|max:100|unique:users,username',
            'email'          => 'required|email|max:255|unique:users,email',
            'contact_number' => 'nullable|string|max:20',
            'role_id'        => 'required|integer|exists:roles,role_id',
            'household_id'   => 'nullable|string|exists:households,household_id',
            'password'       => 'required|string|min:8|confirmed',
        ]);

        try {
            $role = Role::findOrFail($validated['role_id']);
            $this->assertHouseholdAssigned($role, $validated);

            User::create([
                'name'                 => $validated['name'],
                'username'             => $validated['username'],
                'email'                => $validated['email'],
                'contact_number'       => $validated['contact_number'] ?? null,
                'password'             => Hash::make($validated['password']),
                'role_id'              => $validated['role_id'],
                'household_id'         => $validated['household_id'] ?? null,
                'is_active'            => true,
                'must_change_password' => false,
            ]);

            return redirect()->route('admin.accounts.index')
                ->with('success', "Account for '{$validated['name']}' created successfully!");
        } catch (\Exception $e) {
            \Log::error('Account creation error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create account: ' . $e->getMessage());
        }
    }

    public function edit(User $user)
    {
        abort_unless(auth()->user()?->canManageAccounts(), 403, 'You are not authorized to edit accounts.');

        return view('admin.accounts.edit', [
            'user'       => $user,
            'households' => Household::orderBy('household_code')->get(),
            'roles'      => $this->manageableRoles(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        abort_unless(auth()->user()?->canManageAccounts(), 403, 'You are not authorized to update accounts.');

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'username'       => 'required|string|max:100|unique:users,username,' . $user->user_id . ',user_id',
            'email'          => 'required|email|unique:users,email,' . $user->user_id . ',user_id',
            'contact_number' => 'nullable|string|max:20',
            'role_id'        => 'required|integer|exists:roles,role_id',
            'household_id'   => 'nullable|string|exists:households,household_id',
            'is_active'      => 'boolean',
            'password'       => 'nullable|string|min:8|confirmed',
        ]);

        try {
            $role = Role::find($validated['role_id']);
            $this->assertHouseholdAssigned($role, $validated);

            $updateData = [
                'name'           => $validated['name'],
                'username'       => $validated['username'],
                'email'          => $validated['email'],
                'contact_number' => $validated['contact_number'] ?? null,
                'role_id'        => $validated['role_id'],
                'household_id'   => $validated['household_id'] ?? null,
                'is_active'      => $validated['is_active'] ?? true,
            ];

            if (!empty($validated['password'])) {
                $updateData['password']             = Hash::make($validated['password']);
                $updateData['must_change_password'] = false;
            }

            $user->update($updateData);

            return redirect()->route('admin.accounts.index')
                ->with('success', "Account for '{$user->name}' updated successfully!");
        } catch (\Exception $e) {
            report($e);
            return back()->withInput()->with('error', 'Failed to update account. ' . $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        abort_unless(
            auth()->user()?->isCaptain() || auth()->user()?->isSuperAdmin(),
            403,
            'You do not have permission to delete accounts.'
        );
        abort_if($user->is(auth()->user()), 403, 'You cannot delete your own account.');

        try {
            $name = $user->name;
            $user->delete();
            return redirect()->route('admin.accounts.index')
                ->with('success', "Account for '{$name}' deleted successfully!");
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to delete account. ' . $e->getMessage());
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function manageableRoles()
    {
        return Role::whereIn('name', self::MANAGEABLE_ROLES)
            ->get()
            ->unique(fn($r) => strtolower($r->name))
            ->values();
    }

    private function assertHouseholdAssigned(?Role $role, array $validated): void
    {
        if (strtolower($role?->name ?? '') === 'household' && empty($validated['household_id'])) {
            abort(422, 'Please select a household for this Household account.');
        }
    }
}
