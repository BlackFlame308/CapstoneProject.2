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
            $table->id();
            $table->foreignId('data_source_id')->constrained('data_sources');
            $table->string('file_name', 255)->nullable();
            $table->integer('total_records')->nullable();
            $table->integer('successful_records')->nullable();
            $table->integer('failed_records')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();
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
