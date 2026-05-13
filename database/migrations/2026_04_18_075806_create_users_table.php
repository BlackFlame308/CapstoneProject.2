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
            $table->string('user_id', 255)->primary();
            $table->string('name', 100)->nullable();
            $table->string('username', 100)->nullable()->unique();
            $table->string('email', 255)->nullable()->unique();
            $table->string('password', 255);
            $table->string('contact_number', 50)->nullable();
            $table->integer('role_id')->unsigned()->constrained('roles', 'role_id');
            $table->string('assigned_center_id', 255)->nullable();
            $table->string('household_id', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('deleted_at')->nullable();

            $table->foreign('role_id')->references('role_id')->on('roles');
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
