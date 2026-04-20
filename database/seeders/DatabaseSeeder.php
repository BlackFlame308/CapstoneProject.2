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
        // First, seed roles and permissions
        $this->call(RoleSeeder::class);

        // Fetch roles
        $captainRole = Role::where('name', 'Captain')->first();
        $encoderRole = Role::where('name', 'Encoder')->first();

        // Create a Captain user
        $captain = User::firstOrCreate(
            ['email' => 'captain@safetrack.local'],
            [
                'name' => 'Barangay Captain',
                'password' => bcrypt('password'),
                'role_id' => $captainRole->id,
            ]
        );

        // Create an Encoder user
        $encoder = User::firstOrCreate(
            ['email' => 'encoder@safetrack.local'],
            [
                'name' => 'Data Encoder',
                'password' => bcrypt('password'),
                'role_id' => $encoderRole->id,
            ]
        );

        // Optional: Ensure roles are synced with permissions again just in case
        // $captainRole->permissions()->sync(\App\Models\Permission::all()->pluck('id'));
        // $encoderRole->permissions()->sync(\App\Models\Permission::whereIn('name', [
        //     'add_households',
        //     'update_households',
        //     'view_households',
        //     'manage_users',
        //     'view_analytics'
        // ]));
    }
}