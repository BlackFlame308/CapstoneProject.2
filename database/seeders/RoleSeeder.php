<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Roles table now uses: role_id (int PK), name (varchar 50)
     */
    public function run(): void
    {
        $roles = [
            'Super Admin',
            'Admin',
            'Captain',
            'Encoder',
            'Household',
            'Moderator',
            'personel',
        ];

        foreach ($roles as $roleName) {
            Role::updateOrCreate(
                ['name' => $roleName],
                ['name' => $roleName]
            );
        }
    }
}
