<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100)->index();
            $table->string('code', 50)->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('provinces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('region_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100)->index();
            $table->string('code', 50)->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('province_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100)->index();
            $table->string('code', 50)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['province_id', 'name']); // unique within province
        });

        Schema::create('barangays', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('city_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100)->index();
            $table->string('code', 20)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['city_id', 'name']); // unique within city
        });

        Schema::create('sitios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('barangay_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['barangay_id', 'name']); // unique within barangay
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sitios');
        Schema::dropIfExists('barangays');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('regions');
    }
};
