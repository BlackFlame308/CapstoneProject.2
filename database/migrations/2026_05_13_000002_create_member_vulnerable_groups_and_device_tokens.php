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
        // Member vulnerable groups (many-to-many relationship)
        Schema::create('member_vulnerable_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('member_id', 255);
            $table->unsignedInteger('vulnerable_group_id');

            $table->foreign('member_id')->references('member_id')->on('members')->cascadeOnDelete();
            $table->foreign('vulnerable_group_id')->references('vulnerable_group_id')->on('vulnerable_groups')->cascadeOnDelete();

            $table->unique(['member_id', 'vulnerable_group_id']);
        });

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
        Schema::dropIfExists('member_vulnerable_groups');
    }
};
