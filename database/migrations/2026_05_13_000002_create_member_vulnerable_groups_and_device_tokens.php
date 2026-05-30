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
        // Device Tokens
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('household_id', 255);
            $table->string('player_id', 255)->unique();
            $table->unsignedInteger('battery_level')->nullable();
            $table->unsignedInteger('signal_strength')->nullable();
            $table->dateTime('logged_at')->nullable();
            $table->timestamps();

            $table->foreign('household_id')->references('household_id')->on('households')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
