<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Addresses now link to the unified locations table.
     * location_id points to a barangay (or sitio if required).
     */
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('street', 255)->nullable();
            $table->string('purok_sitio', 150)->nullable(); // Replaces purok, sitio_id, and sitio_name
            $table->string('house_number', 100)->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('full_address', 500)->nullable();

            $table->foreignUuid('barangay_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();
            
            // Allow manual text input for unseeded barangay
            $table->string('barangay_name', 100)->nullable();

            $table->timestamps();
            $table->softDeletes();

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

