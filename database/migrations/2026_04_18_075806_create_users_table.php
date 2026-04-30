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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('username', 100)->nullable()->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('contact_number', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('role_id')->constrained('roles');
            $table->foreignUuid('household_id')->nullable()->unique();
            $table->boolean('must_change_password')->default(true);
            $table->string('temp_password')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
