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
        if (User::count() > 0 && (!auth()->check() || !auth()->user()->can('register_accounts'))) {
            return redirect()->route('login')->with('error', 'You are not authorized to register a new account.');
        }

        $roles = [];

        if (auth()->check() && auth()->user()->can('register_accounts')) {
            if (auth()->user()->isCaptain()) {
                $roles = Role::whereIn('name', ['Captain', 'Encoder', 'Household'])->get();
            } else {
                $roles = Role::whereIn('name', ['Encoder', 'Household'])->get();
            }
        }

        return view('auth.register', compact('roles'));
    }

    public function store(Request $request)
    {
        if (User::count() > 0 && (!auth()->check() || !auth()->user()->can('register_accounts'))) {
            abort(403, 'You are not authorized to create user accounts.');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ];

        if (User::count() > 0) {
            $rules['role_id'] = 'required|exists:roles,id';
        }

        $validated = $request->validate($rules);

        if (User::count() === 0) {
            $role = Role::firstOrCreate(['name' => 'Captain']);
        } else {
            $role = Role::find($validated['role_id']);

            if (!auth()->user()->isCaptain() && $role->name === 'Captain') {
                abort(403, 'Only a Captain can create another Captain account.');
            }
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $role->id,
            'must_change_password' => false,
        ]);

        if (User::count() === 1) {
            Auth::login($user);
        }

        return redirect()->route('dashboard')->with('success', 'User account created successfully.');
    }
}
