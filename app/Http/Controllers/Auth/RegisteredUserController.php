<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    public function create()
    {
        $isFirstUser = User::count() === 0;

        if (!$isFirstUser && (!auth()->check() || !auth()->user()->can('register_accounts'))) {
            abort(403, 'You are not authorized to register a new account.');
        }

        if ($isFirstUser) {
            $roles = Role::where('name', 'Captain')->get();
        } elseif (auth()->user()->isCaptain()) {
            $roles = Role::whereIn('name', ['Captain', 'Encoder', 'Household'])->get();
        } else {
            $roles = Role::whereIn('name', ['Encoder', 'Household'])->get();
        }

        return Inertia::render('Auth/Register', [
            'roles'       => $roles,
            'isFirstUser' => $isFirstUser,
        ]);
    }

    public function store(Request $request)
    {
        $isFirstUser = User::count() === 0;

        if (!$isFirstUser && (!auth()->check() || !auth()->user()->can('register_accounts'))) {
            abort(403, 'You are not authorized to create user accounts.');
        }

        $rules = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ];

        if (!$isFirstUser) {
            $rules['role_id'] = 'required|exists:roles,id';
        }

        $validated = $request->validate($rules);

        if ($isFirstUser) {
            $role = Role::where('name', 'Captain')->first();
            if (!$role) {
                abort(500, 'Captain role not configured. Run seeders.');
            }
        } else {
            $role = Role::find($validated['role_id']);

            $allowedRoles = auth()->user()->isCaptain()
                ? ['Captain', 'Encoder', 'Household']
                : ['Encoder', 'Household'];

            if (!in_array($role->name, $allowedRoles)) {
                abort(403, 'You are not authorized to assign this role.');
            }
        }

        $user = User::create([
            'name'                 => $validated['name'],
            'email'                => $validated['email'],
            'password'             => Hash::make($validated['password']),
            'role_id'              => $role->id,
            'must_change_password' => false,
        ]);

        // Only auto-login the very first user (the Captain bootstrap)
        if ($isFirstUser) {
            Auth::login($user);
        }

        return redirect()->route('dashboard')->with('success', 'User account created successfully.');
    }
}
