<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Analytics now reference the location hierarchy with proper foreign keys.
     */
    public function up(): void
    {
        Schema::create('analytics', function (Blueprint $table) {
            $table->string('analytic_id', 255)->primary();
            $table->unsignedInteger('barangay_id')->nullable();
            $table->unsignedInteger('purok_id')->nullable();
            $table->unsignedInteger('sitio_id')->nullable();
            $table->unsignedInteger('total_population')->default(0);
            $table->unsignedInteger('total_household')->default(0);
            $table->unsignedInteger('children_count')->default(0);
            $table->unsignedInteger('adult_count')->default(0);
            $table->unsignedInteger('elderly_count')->default(0);
            $table->unsignedInteger('pwd_count')->default(0);
            $table->unsignedInteger('pregnant_count')->default(0);
            $table->unsignedInteger('male_count')->default(0);
            $table->unsignedInteger('female_count')->default(0);
            $table->dateTime('recorded_at');
            $table->timestamps();

            $table->foreign('barangay_id')->references('barangay_id')->on('barangays')->nullOnDelete();
            $table->foreign('purok_id')->references('purok_id')->on('puroks')->nullOnDelete();
            $table->foreign('sitio_id')->references('sitio_id')->on('sitios')->nullOnDelete();

            $table->index(['barangay_id', 'purok_id', 'sitio_id', 'recorded_at']);
            $table->index(['barangay_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics');
    }
};

