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
        Schema::create('import_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('data_source_id');
            $table->unsignedInteger('row_number')->nullable();
            $table->string('status', 20)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('data_source_id')->references('id')->on('data_sources')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
