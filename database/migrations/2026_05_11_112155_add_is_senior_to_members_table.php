<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('household_members', function (Blueprint $table) {
            if (!Schema::hasColumn('household_members', 'is_senior')) {
                $table->boolean('is_senior')->default(false)->after('is_graduate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('household_members', function (Blueprint $table) {
            if (Schema::hasColumn('household_members', 'is_senior')) {
                $table->dropColumn('is_senior');
            }
        });
    }
};
