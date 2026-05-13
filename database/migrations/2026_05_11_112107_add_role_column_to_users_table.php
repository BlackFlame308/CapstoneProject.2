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
        // Role column is now handled by role_id foreign key in the base users table
        // This migration is kept for historical reference but is now a no-op
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
