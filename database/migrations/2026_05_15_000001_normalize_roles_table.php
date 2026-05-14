<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'name')) {
                $table->string('name', 50)->nullable();
            }
        });

        if (Schema::hasColumn('roles', 'role_name')) {
            DB::table('roles')
                ->whereNull('name')
                ->update(['name' => DB::raw('role_name')]);
        }
    }

    public function down(): void
    {
        // Kept intentionally non-destructive for existing databases.
    }
};
