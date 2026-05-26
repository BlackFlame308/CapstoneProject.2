<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $captainRole = $this->role('captain', 'Captain');
        $this->role('encoder', 'Encoder');
        $this->role('household', 'Household');

        // Create test captain user
        User::firstOrCreate(
            ['email' => 'captain@safetrack.local'],
            $this->userAttributes([
                'name' => 'Captain Test',
                'password' => bcrypt('password'),
                'role_id' => $captainRole->role_id,
                'must_change_password' => false
            ])
        );

        $this->command?->info('Test data seeded successfully!');
    }

    private function role(string $key, string $name): Role
    {
        $nameColumn = Schema::hasColumn('roles', 'role_name') ? 'role_name' : 'name';

        if (Schema::hasColumn('roles', 'role_key')) {
            return Role::updateOrCreate(['role_key' => $key], [$nameColumn => $name]);
        }

        return Role::updateOrCreate([$nameColumn => $name], [$nameColumn => $name]);
    }

    private function userAttributes(array $attributes): array
    {
        if (!Schema::hasColumn('users', 'must_change_password')) {
            unset($attributes['must_change_password']);
        }

        return $attributes;
    }
}
