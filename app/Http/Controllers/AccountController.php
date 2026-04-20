<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        abort_if(! auth()->user()->can('manage_accounts'), 403);

        $users = User::with('role')->orderBy('created_at', 'desc')->paginate(15);

        return view('accounts.index', compact('users'));
    }
}
