<?php

namespace App\Providers;

use App\Models\Household;
use App\Models\Member;
use App\Policies\HouseholdPolicy;
use App\Policies\MemberPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);

        Gate::policy(Household::class, HouseholdPolicy::class);
        Gate::policy(Member::class, MemberPolicy::class);
    }
}
