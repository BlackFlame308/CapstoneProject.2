<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $user = auth()->user();

        // Get role name from relationship
        $role = strtolower($user->role?->name ?? '');

        // Allow dashboard roles. "head" is kept for legacy seeded data.
        if (!$role || !in_array($role, ['admin', 'super admin', 'head', 'encoder', 'captain'], true)) {
            return redirect()->route('login')->with('error', 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
