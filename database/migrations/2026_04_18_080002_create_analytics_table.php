<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Analytics now reference the unified locations table.
     * location_id can point to barangay or sitio for granular reporting.
     */
    public function up(): void
    {
        Schema::create('analytics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('barangay_id')
                  ->nullable()
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('purok_sitio', 150)->nullable();
            $table->date('record_period')->comment('First day of the month for monthly snapshots');
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
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['barangay_id', 'purok_sitio', 'record_period'], 'analytics_location_period_index');
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

