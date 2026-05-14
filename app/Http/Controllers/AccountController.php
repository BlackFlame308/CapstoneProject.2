<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('manage_accounts', User::class);

        return redirect()->route('admin.accounts.index', $request->query());
    }
}
