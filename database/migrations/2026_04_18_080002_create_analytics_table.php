<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Analytics reference the location hierarchy with proper foreign keys.
     */
    public function up(): void
    {
        Schema::create('analytics', function (Blueprint $table) {
            $table->string('analytic_id', 255)->primary();
            $table->unsignedInteger('barangay_id')->nullable();
            $table->string('purok_sitio', 150)->nullable();
            $table->date('record_period')->nullable();
            $table->unsignedInteger('total_households')->default(0);
            $table->unsignedInteger('total_population')->default(0);
            $table->unsignedInteger('total_males')->default(0);
            $table->unsignedInteger('total_females')->default(0);
            $table->unsignedInteger('total_pwd')->default(0);
            $table->unsignedInteger('total_seniors')->default(0);
            $table->unsignedInteger('total_children')->default(0);
            $table->unsignedInteger('total_adults')->default(0);
            $table->unsignedInteger('total_pregnant')->default(0);
            $table->unsignedInteger('total_evacuees')->default(0);
            $table->timestamps();

            $table->foreign('barangay_id')->references('barangay_id')->on('barangays')->nullOnDelete();

            $table->index(['barangay_id', 'record_period']);
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
