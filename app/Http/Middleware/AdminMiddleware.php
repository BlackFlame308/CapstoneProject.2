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

        // Use normalizedRole() so this works whether the role comes from the
        // `role()` relationship OR the raw `role` attribute.
        $role = strtolower($user->normalizedRole() ?? '');

        // Allow dashboard roles. "head" is kept for legacy seeded data.
        if (!$role || !in_array($role, ['admin', 'super admin', 'head', 'encoder', 'captain'], true)) {
            return redirect()->route('login')->with('error', 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
