<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('roles')) {
            $hasRoleKey = Schema::hasColumn('roles', 'role_key');
            $hasRoleName = Schema::hasColumn('roles', 'role_name');

            // Update evac_admin (ID 2) to moderator
            $updateData = ['name' => 'Moderator'];
            if ($hasRoleKey) {
                $updateData['role_key'] = 'moderator';
            }
            if ($hasRoleName) {
                $updateData['role_name'] = 'Moderator';
            }

            $query = DB::table('roles')->where('role_id', 2);
            if ($hasRoleKey) {
                $query->orWhere('role_key', 'evac_admin');
            }
            $query->update($updateData);

            // Update evac_personnel (ID 3) to personel
            $updateData2 = ['name' => 'personel'];
            if ($hasRoleKey) {
                $updateData2['role_key'] = 'personel';
            }
            if ($hasRoleName) {
                $updateData2['role_name'] = 'personel';
            }

            $query2 = DB::table('roles')->where('role_id', 3);
            if ($hasRoleKey) {
                $query2->orWhere('role_key', 'evac_personnel')
                       ->orWhere('role_key', 'evac_personel');
            }
            $query2->update($updateData2);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('roles')) {
            $hasRoleKey = Schema::hasColumn('roles', 'role_key');
            $hasRoleName = Schema::hasColumn('roles', 'role_name');

            // Revert evac_admin (ID 2)
            $updateData = ['name' => 'Evacuation Center Admin'];
            if ($hasRoleKey) {
                $updateData['role_key'] = 'evac_admin';
            }
            if ($hasRoleName) {
                $updateData['role_name'] = 'Encoder';
            }

            $query = DB::table('roles')->where('role_id', 2);
            if ($hasRoleKey) {
                $query->orWhere('role_key', 'moderator');
            }
            $query->update($updateData);

            // Revert evac_personnel (ID 3)
            $updateData2 = ['name' => 'Encoder'];
            if ($hasRoleKey) {
                $updateData2['role_key'] = 'evac_personnel';
            }
            if ($hasRoleName) {
                $updateData2['role_name'] = 'Encoder';
            }

            $query2 = DB::table('roles')->where('role_id', 3);
            if ($hasRoleKey) {
                $query2->orWhere('role_key', 'personel')
                       ->orWhere('role_key', 'personnel');
            }
            $query2->update($updateData2);
        }
    }
};
