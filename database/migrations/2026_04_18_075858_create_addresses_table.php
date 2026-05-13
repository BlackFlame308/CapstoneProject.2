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
            $table->increments('address_id');
            $table->string('street_address', 255)->nullable();
            $table->unsignedInteger('barangay_id')->nullable();
            $table->unsignedInteger('sitio_id')->nullable();
            $table->unsignedInteger('purok_id')->nullable();
            $table->unsignedInteger('zipcode_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('barangay_id')->references('barangay_id')->on('barangays')->nullOnDelete();
            $table->foreign('sitio_id')->references('sitio_id')->on('sitios')->nullOnDelete();
            $table->foreign('purok_id')->references('purok_id')->on('puroks')->nullOnDelete();
            $table->foreign('zipcode_id')->references('zipcode_id')->on('zipcodes')->nullOnDelete();

            $table->index(['barangay_id', 'deleted_at']);
            $table->index('sitio_id');
            $table->index('purok_id');
            $table->index('zipcode_id');
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

