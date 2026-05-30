<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/**
 * AccountAdminController
 * 
 * Manages user accounts in the system
 * 
 * FEATURES:
 * - View all user accounts
 * - Create new account (for Household role only)
 * - Edit account
 * - Delete account
 * - Assign roles
 * 
 * ACCOUNT TYPES:
 * - Household: Regular household member/head
 * 
 * NOTE: Does NOT create Responder or Officer accounts
 * Those are managed separately by their respective systems
 * 
 * ROLES AVAILABLE:
 * - 'head': Barangay Head (full access to admin dashboard)
 * - 'encoder': Encoder (can create/view/edit, cannot delete)
 * - 'household': Household user (can view own household)
 */
class AccountAdminController extends Controller
{
    /**
     * List all user accounts
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->canManageAccounts(), 403, 'You are not authorized to manage accounts.');

        $query = User::with(['role', 'household']);

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('role', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Search by name, email, or username
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        
        // Get roles for filter
        $roles = Role::where('name', '!=', 'Household')->orderBy('name')->get();

        return view('admin.accounts.index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    /**
     * Show form to create new account
     */
    public function create()
    {
        abort_unless(auth()->user()?->canManageAccounts(), 403, 'You are not authorized to create accounts.');

        $households = Household::orderBy('household_code')->get();
        $roles = Role::whereIn('name', ['Captain', 'Encoder', 'Household'])->get();

        return view('admin.accounts.create', [
            'households' => $households,
            'roles' => $roles,
        ]);
    }

    /**
     * Store new user account
     */
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

            // If the role is 'household', a household must be selected
            if (strtolower($role->name) === 'household' && empty($validated['household_id'])) {
                return back()->withInput()
                    ->with('error', 'Please select a household for this Household account.');
            }

            // Map role name to the enum column value used by the system
            $roleEnum = match(strtolower($role->name)) {
                'captain' => 'head',
                'encoder' => 'encoder',
                default   => 'resident',
            };

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
            return back()->withInput()
                ->with('error', 'Failed to create account: ' . $e->getMessage());
        }
    }

    /**
     * Show edit form for account
     */
    public function edit(User $user)
    {
        abort_unless(auth()->user()?->canManageAccounts(), 403, 'You are not authorized to edit accounts.');

        $households = Household::orderBy('household_code')->get();
        $roles = Role::whereIn('name', ['Captain', 'Encoder', 'Household'])->get();

        return view('admin.accounts.edit', [
            'user' => $user,
            'households' => $households,
            'roles' => $roles,
        ]);
    }

    /**
     * Update user account
     */
    public function update(Request $request, User $user)
    {
        abort_unless(auth()->user()?->canManageAccounts(), 403, 'You are not authorized to update accounts.');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:users,username,' . $user->user_id . ',user_id',
            'email' => 'required|email|unique:users,email,' . $user->user_id . ',user_id',
            'contact_number' => 'nullable|string|max:20',
                        'role_id' => 'required|integer|exists:roles,role_id',
            'household_id' => 'nullable|string|exists:households,household_id',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        try {
            $role = Role::find($validated['role_id']);
            if (strtolower($role?->name ?? '') === 'household' && empty($validated['household_id'])) {
                return back()->withInput()
                    ->with('error', 'Household user must be assigned to a household.');
            }

            // Prepare update data
            $updateData = [
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'contact_number' => $validated['contact_number'] ?? null,
                'role_id' => $validated['role_id'],
                'household_id' => $validated['household_id'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ];

            // Update password if provided
            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
                $updateData['must_change_password'] = false;
            }

            $user->update($updateData);

            return redirect()->route('admin.accounts.index')
                ->with('success', "Account for '{$user->name}' updated successfully!");
        } catch (\Exception $e) {
            report($e);
            return back()->withInput()
                ->with('error', 'Failed to update account. ' . $e->getMessage());
        }
    }

    /**
     * Delete user account
     */
    public function destroy(User $user)
    {
        abort_unless(auth()->user()?->canManageAccounts(), 403, 'You are not authorized to delete accounts.');
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
}
