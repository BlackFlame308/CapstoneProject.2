<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vulnerable Groups
        Schema::create('vulnerable_groups', function (Blueprint $table) {
            $table->increments('vulnerable_group_id');
            $table->string('vulnerable_group_key', 50)->unique();
            $table->string('vulnerable_group_label', 100);
        });

        DB::table('vulnerable_groups')->insert([
            ['vulnerable_group_id' => 1, 'vulnerable_group_key' => 'elderly', 'vulnerable_group_label' => 'Elderly'],
            ['vulnerable_group_id' => 2, 'vulnerable_group_key' => 'children', 'vulnerable_group_label' => 'Children'],
            ['vulnerable_group_id' => 3, 'vulnerable_group_key' => 'pwd', 'vulnerable_group_label' => 'PWD'],
            ['vulnerable_group_id' => 4, 'vulnerable_group_key' => 'pregnant', 'vulnerable_group_label' => 'Pregnant Women'],
            ['vulnerable_group_id' => 5, 'vulnerable_group_key' => 'indigenous', 'vulnerable_group_label' => 'Indigenous People'],
        ]);

        // Member Vulnerable Groups junction
        Schema::create('member_vulnerable_groups', function (Blueprint $table) {
            $table->string('member_id', 255);
            $table->unsignedInteger('vulnerable_group_id');
            $table->primary(['member_id', 'vulnerable_group_id']);

            $table->foreign('member_id')->references('member_id')->on('household_members')->cascadeOnDelete();
            $table->foreign('vulnerable_group_id')->references('vulnerable_group_id')->on('vulnerable_groups')->cascadeOnDelete();
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
        Schema::dropIfExists('vulnerable_groups');
    }
};
