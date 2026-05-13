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
            $table->string('household_id', 255)->primary();
            $table->string('household_code', 255)->unique();
            $table->string('household_name', 100);
            $table->unsignedInteger('address_id')->nullable();
            $table->string('contact_number', 50)->nullable();
            $table->string('emergency_contact', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('address_id')->references('address_id')->on('addresses')->cascadeOnDelete();
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
