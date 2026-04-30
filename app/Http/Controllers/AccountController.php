<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('manage_accounts', User::class);

        $users = User::with('role')
            ->when($request->search, fn($q, $search) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            )
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Account/Index', [
            'users' => $users,
            'filters' => $request->only('search'),
        ]);
    }
}
