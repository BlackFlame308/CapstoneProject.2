<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->increments('region_id');
            $table->string('name', 100);
            $table->string('code', 50)->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('provinces', function (Blueprint $table) {
            $table->increments('province_id');
            $table->unsignedInteger('region_id');
            $table->string('name', 100);
            $table->string('code', 50)->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('region_id')->references('region_id')->on('regions')->cascadeOnDelete();
            $table->index('region_id');
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->increments('city_id');
            $table->unsignedInteger('province_id');
            $table->string('name', 100);
            $table->string('code', 50)->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('province_id')->references('province_id')->on('provinces')->cascadeOnDelete();
            $table->index('province_id');
        });

        Schema::create('barangays', function (Blueprint $table) {
            $table->increments('barangay_id');
            $table->unsignedInteger('city_id');
            $table->string('name', 100);
            $table->string('code', 50)->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('city_id')->references('city_id')->on('cities')->cascadeOnDelete();
            $table->index('city_id');
        });

        Schema::create('sitios', function (Blueprint $table) {
            $table->increments('sitio_id');
            $table->unsignedInteger('barangay_id');
            $table->string('name', 100);
            $table->string('code', 50)->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('barangay_id')->references('barangay_id')->on('barangays')->cascadeOnDelete();
            $table->index('barangay_id');
        });

        Schema::create('puroks', function (Blueprint $table) {
            $table->increments('purok_id');
            $table->unsignedInteger('sitio_id');
            $table->string('name', 100);
            $table->string('code', 50)->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('sitio_id')->references('sitio_id')->on('sitios')->cascadeOnDelete();
            $table->index('sitio_id');
        });

        Schema::create('zipcodes', function (Blueprint $table) {
            $table->increments('zipcode_id');
            $table->unsignedInteger('city_id');
            $table->string('zipcode', 10)->unique();
            $table->timestamps();

            $table->foreign('city_id')->references('city_id')->on('cities')->cascadeOnDelete();
            $table->index('city_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zipcodes');
        Schema::dropIfExists('puroks');
        Schema::dropIfExists('sitios');
        Schema::dropIfExists('barangays');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('regions');
    }
};
