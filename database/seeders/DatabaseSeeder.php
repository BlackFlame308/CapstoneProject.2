<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

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

        $captainRole = Role::where('name', 'Captain')->firstOrFail();
        $encoderRole = Role::where('name', 'Encoder')->firstOrFail();

        User::updateOrCreate(
            ['email' => 'captain@safetrack.local'],
            [
                'name'                 => 'Barangay Captain',
                'password'             => bcrypt('password'),
                'role_id'              => $captainRole->role_id,
                'must_change_password' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'encoder@safetrack.local'],
            [
                'name'                 => 'Data Encoder',
                'password'             => bcrypt('password'),
                'role_id'              => $encoderRole->role_id,
                'must_change_password' => false,
            ]
        );

        $this->call(TestDataSeeder::class);
    }
}
