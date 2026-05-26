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
        Schema::create('csv_uploads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('data_source_id');
            $table->string('file_name', 255)->nullable();
            $table->unsignedInteger('total_records')->nullable();
            $table->unsignedInteger('successful_records')->nullable();
            $table->unsignedInteger('failed_records')->nullable();
            $table->timestamps();

            $table->foreign('data_source_id')->references('id')->on('data_sources')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('csv_uploads');
    }
};
