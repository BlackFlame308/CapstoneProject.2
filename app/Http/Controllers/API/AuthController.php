<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
public function register(Request $request): JsonResponse
    {
        // Only Captain may create accounts (Super Admin check covers Captain role too)
        if (!auth()->check() || !auth()->user()->isCaptain()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Forbidden: only Captain can create accounts.',
            ], 403);
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id'  => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'name'                 => $validated['name'],
            'email'                => $validated['email'],
            'password'             => Hash::make($validated['password']),
            'role_id'              => $validated['role_id'],
            'must_change_password' => false,
        ]);

        $user->load('role');

        return response()->json([
            'status'  => 'success',
            'message' => 'User registered successfully',
            'data'    => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user->load('role');

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Login successful',
            'data'    => [
                'user'  => $user,
                'token' => $token,
            ],
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user?->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Logged out successfully',
        ], 200);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Current password is incorrect',
            ], 403);
        }

        $user->update([
            'password'             => Hash::make($validated['new_password']),
            'must_change_password' => false,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Password changed successfully',
        ], 200);
    }
}