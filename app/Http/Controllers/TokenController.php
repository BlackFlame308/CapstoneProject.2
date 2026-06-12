<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class TokenController extends Controller
{
    public function index(Request $request)
    {
        $tokens = DB::table('personal_access_tokens')
            ->join('users', 'personal_access_tokens.tokenable_id', '=', 'users.user_id')
            ->select('personal_access_tokens.*', 'users.email as user_email', 'users.name as user_name')
            ->orderByDesc('personal_access_tokens.created_at')
            ->get();

        $users = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['Captain', 'Encoder', 'Moderator', 'personel', 'personnel']);
        })->orderBy('name')->get();

        return view('admin.tokens.index', compact('tokens', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'token_name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,user_id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $tokenResult = $user->createToken($validated['token_name']);
        $plainTextToken = $tokenResult->plainTextToken;

        return redirect()->route('admin.tokens.index')
            ->with('success', 'API Token created successfully!')
            ->with('plain_text_token', $plainTextToken);
    }

    public function destroy($id)
    {
        DB::table('personal_access_tokens')->where('id', $id)->delete();

        return redirect()->route('admin.tokens.index')
            ->with('success', 'API Token revoked successfully!');
    }
}
