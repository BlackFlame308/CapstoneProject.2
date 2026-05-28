<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * Supports single or pipe-separated roles:
     *   middleware('role:Captain')
     *   middleware('role:Captain|Encoder')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->normalizedRole()) {
            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => 'No role assigned.'], 403)
                : abort(403, 'No role assigned to this account.');
        }

        // Flatten pipe-separated roles passed as a single string e.g. 'Captain|Encoder'
        $allowed = collect($roles)
            ->flatMap(fn($r) => explode('|', $r))
            ->map('trim')
            ->all();

        $roleName = $user->normalizedRole();
        $allowed = array_map(fn ($role) => strtolower($role), $allowed);

        // Treat legacy-equivalent role names as the same access group.
        // This avoids route failures when one part uses "captain" and another uses "head".
        if ($roleName === 'head' && in_array('captain', $allowed, true)) {
            return $next($request);
        }

        if ($roleName === 'captain' && in_array('head', $allowed, true)) {
            return $next($request);
        }

        if (!in_array($roleName, $allowed, true)) {
            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => 'Forbidden: insufficient role.'], 403)
                : abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
