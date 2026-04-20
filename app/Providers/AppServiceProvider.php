<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('view_households', function (?User $user) {
            return $user && ($user->isCaptain() || $user->isEncoder());
        });

        Gate::define('manage_households', function (?User $user) {
            return $user && ($user->isCaptain() || $user->isEncoder());
        });

        Gate::define('view_reports', function (?User $user) {
            return $user && ($user->isCaptain() || $user->isEncoder());
        });

        Gate::define('manage_accounts', function (?User $user) {
            return $user && ($user->isCaptain() || $user->isEncoder());
        });

        Gate::define('register_accounts', function (?User $user) {
            return $user && ($user->isCaptain() || $user->isEncoder());
        });

        Gate::define('delete_accounts', function (?User $user) {
            return $user && $user->isCaptain();
        });
    }
}
