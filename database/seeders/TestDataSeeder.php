<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        Role::firstOrCreate(['name' => 'Captain']);
        Role::firstOrCreate(['name' => 'Encoder']);
        Role::firstOrCreate(['name' => 'Household']);

        // Create test captain user
        User::firstOrCreate(
            ['email' => 'captain@safetrack.local'],
            [
                'name' => 'Captain Test',
                'password' => bcrypt('password'),
                'role_id' => 1,
                'must_change_password' => false
            ]
        );

        echo "Test data seeded successfully!\n";
    }
}