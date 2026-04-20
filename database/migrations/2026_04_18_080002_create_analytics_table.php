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
        Schema::create('analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barangay_id')->constrained('barangays');
            $table->string('sitio', 100)->nullable();
            $table->integer('total_households')->nullable();
            $table->integer('total_population')->nullable();
            $table->integer('total_pwd')->nullable();
            $table->integer('total_seniors')->nullable();
            $table->timestamp('created_at')->useCurrent();
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
