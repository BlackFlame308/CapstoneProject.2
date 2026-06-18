<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds first_name and last_name columns to the users table,
     * which are required by the User model's setNameAttribute mutator.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name', 100)->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name', 100)->nullable()->after('first_name');
            }
        });

        // Backfill existing rows: split the name column into first/last name
        DB::table('users')->get()->each(function ($user) {
            $parts = explode(' ', trim((string) $user->name), 2);
            DB::table('users')->where('user_id', $user->user_id)->update([
                'first_name' => $parts[0] ?? '',
                'last_name'  => $parts[1] ?? '',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
