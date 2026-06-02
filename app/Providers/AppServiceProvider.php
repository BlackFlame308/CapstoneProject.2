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

        $setGrammar = function ($connection) {
            if ($connection->getQueryGrammar() instanceof \Illuminate\Database\Query\Grammars\MySqlGrammar) {
                // Check if already set
                if (!($connection->getQueryGrammar() instanceof \App\Grammar\MappedMySqlGrammar)) {
                    $connection->setQueryGrammar(new class($connection) extends \Illuminate\Database\Query\Grammars\MySqlGrammar {
                        protected function wrapValue($value)
                        {
                            if ($value === 'members') {
                                return parent::wrapValue('household_members');
                            }
                            if ($value === 'members.*') {
                                return parent::wrapValue('household_members.*');
                            }
                            if (str_starts_with($value, 'members.')) {
                                return str_replace('members.', 'household_members.', parent::wrapValue($value));
                            }
                            return parent::wrapValue($value);
                        }

                        public function wrapTable($table, $prefix = null)
                        {
                            $wrapped = parent::wrapTable($table, $prefix);
                            if ($wrapped === '`members`' || $wrapped === 'members') {
                                return parent::wrapTable('household_members', $prefix);
                            }
                            return $wrapped;
                        }
                    });
                }
            }
        };

        try {
            $setGrammar(\Illuminate\Support\Facades\DB::connection());
        } catch (\Throwable $e) {}

        // Listen for new connections
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Database\Events\ConnectionEstablished::class,
            function ($event) use ($setGrammar) {
                $setGrammar($event->connection);
            }
        );

        // Listen before preparing any statement to ensure query grammar is mapped
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Database\Events\StatementPrepared::class,
            function ($event) use ($setGrammar) {
                $setGrammar($event->connection);
            }
        );
    }
}
