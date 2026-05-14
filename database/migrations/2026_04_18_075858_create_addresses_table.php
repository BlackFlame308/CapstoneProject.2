<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Addresses now link to the location hierarchy with proper foreign keys.
     */
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('street', 255)->nullable();
            $table->string('purok_sitio', 150)->nullable();
            $table->string('house_number', 100)->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('full_address', 500)->nullable();
            $table->uuid('barangay_id')->nullable();
            $table->string('barangay_name', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('barangay_id')->references('id')->on('barangays')->nullOnDelete();

            $table->index(['barangay_id', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
