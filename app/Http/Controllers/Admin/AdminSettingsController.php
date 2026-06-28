<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class AdminSettingsController extends Controller
{
    /**
     * Show the unified Settings page (Change Password + API Token Management).
     */
    public function index()
    {
        $user  = auth()->user();
        $canDelete = $user?->canDeleteHouseholds() ?? false;

        // Only load token data for users who have that permission
        $tokens = collect();
        $users  = collect();

        if ($canDelete) {
            $tokens = DB::table('personal_access_tokens')
                ->join('users', 'personal_access_tokens.tokenable_id', '=', 'users.user_id')
                ->select('personal_access_tokens.*', 'users.email as user_email', 'users.name as user_name')
                ->orderByDesc('personal_access_tokens.created_at')
                ->get();

            $users = User::whereHas('role', function ($q) {
                $q->whereIn('name', ['Captain', 'Encoder', 'Moderator', 'personel', 'personnel']);
            })->orderBy('name')->get();
        }

        return view('admin.settings.index', compact('tokens', 'users', 'canDelete'));
    }

    /**
     * Handle password change submitted from the Settings page.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = Auth::user();

        $user->update([
            'password'             => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Password changed successfully.');
    }

    /**
     * Store a new API token (proxied from the Settings page).
     */
    public function storeToken(Request $request)
    {
        $validated = $request->validate([
            'token_name' => 'required|string|max:255',
            'user_id'    => 'required|exists:users,user_id',
        ]);

        $user            = User::findOrFail($validated['user_id']);
        $tokenResult     = $user->createToken($validated['token_name']);
        $plainTextToken  = $tokenResult->plainTextToken;

        return redirect()->route('admin.settings.index')
            ->with('success', 'API Token created successfully!')
            ->with('plain_text_token', $plainTextToken);
    }

    /**
     * Revoke (delete) an API token from the Settings page.
     */
    public function destroyToken($id)
    {
        DB::table('personal_access_tokens')->where('id', $id)->delete();

        return redirect()->route('admin.settings.index')
            ->with('success', 'API Token revoked successfully.');
    }
}
