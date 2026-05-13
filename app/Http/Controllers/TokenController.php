<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Add API token handshake here
        return view('admin.tokens.index');
    }
}
