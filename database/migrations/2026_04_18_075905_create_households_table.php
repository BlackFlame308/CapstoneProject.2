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
        Schema::create('households', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('household_id', 255)->nullable()->unique();
            $table->string('household_code', 255)->unique();
            $table->string('household_name', 100);
            $table->string('email', 150)->nullable()->unique();
            $table->unsignedInteger('member_count')->default(0);
            $table->uuid('address_id')->nullable();
            $table->string('contact_number', 50)->nullable();
            $table->string('emergency_contact', 50)->nullable();
            $table->string('created_by', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('address_id')->references('id')->on('addresses')->nullOnDelete();
            $table->index('household_code');
            $table->index('contact_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('households');
    }
};
