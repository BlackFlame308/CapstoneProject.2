<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure roles exist
        $captainRole = Role::firstOrCreate(['name' => 'Captain']);
        Role::firstOrCreate(['name' => 'Encoder']);
        Role::firstOrCreate(['name' => 'Household']);

        // Create test captain user (uses auto-generated user_id via booted hook)
        User::firstOrCreate(
            ['email' => 'captain@safetrack.local'],
            [
                'name'                 => 'Captain Test',
                'password'             => bcrypt('password'),
                'role_id'              => $captainRole->role_id,
                'must_change_password' => false,
            ]
        );

        $this->command?->info('Test data seeded successfully!');
    }
}
