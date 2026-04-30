<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        
        // Safely build user data with null checks
        $userData = null;
        if ($user) {
            try {
                $userData = [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'role'        => $user->role?->name,
                    'must_change_password' => $user->must_change_password ?? false,
                    'permissions' => [
                        'view_households'   => method_exists($user, 'canViewHouseholds') ? $user->canViewHouseholds() : false,
                        'manage_households' => method_exists($user, 'canManageHouseholds') ? $user->canManageHouseholds() : false,
                        'manage_accounts'   => method_exists($user, 'hasPermission') ? $user->hasPermission('manage_accounts') : false,
                        'view_reports'      => method_exists($user, 'hasPermission') ? $user->hasPermission('view_reports') : false,
                        'register_accounts'  => method_exists($user, 'hasPermission') ? $user->hasPermission('register_accounts') : false,
                    ],
                ];
            } catch (\Throwable $e) {
                // If any error occurs, provide minimal user data
                $userData = [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'role'        => $user->role?->name ?? 'Household',
                    'must_change_password' => $user->must_change_password ?? false,
                    'permissions' => [
                        'view_households'   => false,
                        'manage_households' => false,
                        'manage_accounts'   => false,
                        'view_reports'      => false,
                        'register_accounts' => false,
                    ],
                ];
            }
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $userData,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
            'ziggy' => fn () => [
                'location' => $request->url(),
                'route'    => $request->route()?->getName(),
            ],
        ]);
    }
}
