<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            LocationSeeder::class,
        ]);

        $captainRole = $this->roleByName('Captain');
        $encoderRole = $this->roleByName('Encoder');

        User::updateOrCreate(
            ['email' => 'captain@safetrack.local'],
            $this->userAttributes([
                'name' => 'Barangay Captain',
                'password' => bcrypt('password'),
                'role_id' => $captainRole->role_id,
                'must_change_password' => false,
            ])
        );

        User::updateOrCreate(
            ['email' => 'encoder@safetrack.local'],
            $this->userAttributes([
                'name' => 'Data Encoder',
                'password' => bcrypt('password'),
                'role_id' => $encoderRole->role_id,
                'must_change_password' => false,
            ])
        );

        $this->call(TestDataSeeder::class);
    }

    private function roleByName(string $name): Role
    {
        $nameColumn = Schema::hasColumn('roles', 'role_name') ? 'role_name' : 'name';

        return Role::where($nameColumn, $name)->firstOrFail();
    }

    private function userAttributes(array $attributes): array
    {
        if (!Schema::hasColumn('users', 'must_change_password')) {
            unset($attributes['must_change_password']);
        }

        return $attributes;
    }
}
