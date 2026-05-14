<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\Schema;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['role_key' => 'super_admin', 'role_name' => 'Super Admin'],
            ['role_key' => 'admin', 'role_name' => 'Admin'],
            ['role_key' => 'captain', 'role_name' => 'Captain'],
            ['role_key' => 'encoder', 'role_name' => 'Encoder'],
            ['role_key' => 'household', 'role_name' => 'Household'],
        ];

        $nameColumn = Schema::hasColumn('roles', 'role_name') ? 'role_name' : 'name';

        foreach ($roles as $role) {
            if (Schema::hasColumn('roles', 'role_key')) {
                Role::updateOrCreate(
                    ['role_key' => $role['role_key']],
                    [$nameColumn => $role['role_name']]
                );

                continue;
            }

            Role::updateOrCreate(
                [$nameColumn => $role['role_name']],
                [$nameColumn => $role['role_name']]
            );
        }
    }
}
