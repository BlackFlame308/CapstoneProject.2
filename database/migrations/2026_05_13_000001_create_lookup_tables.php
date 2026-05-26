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
        // Genders
        Schema::create('genders', function (Blueprint $table) {
            $table->increments('gender_id');
            $table->string('gender_key', 20)->unique();
            $table->string('gender_label', 20);
        });

        // Civil Statuses
        Schema::create('civil_statuses', function (Blueprint $table) {
            $table->increments('status_id');
            $table->string('status_key', 20)->unique();
            $table->string('status_label', 20);
        });

        // Education Levels
        Schema::create('education_levels', function (Blueprint $table) {
            $table->increments('education_level_id');
            $table->string('education_level_key', 20)->unique();
            $table->string('education_level_label', 20);
        });

        // Relationships
        Schema::create('relationships', function (Blueprint $table) {
            $table->increments('relationship_id');
            $table->string('relationship_key', 50)->unique();
            $table->string('relationship_label', 100);
        });

        // Vulnerable Groups
        Schema::create('vulnerable_groups', function (Blueprint $table) {
            $table->increments('vulnerable_group_id');
            $table->string('vulnerable_group_key', 20)->unique();
            $table->string('vulnerable_group_label', 20);
        });

        // Occupations
        Schema::create('occupations', function (Blueprint $table) {
            $table->increments('occupation_id');
            $table->string('occupation_name', 100);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occupations');
        Schema::dropIfExists('vulnerable_groups');
        Schema::dropIfExists('relationships');
        Schema::dropIfExists('education_levels');
        Schema::dropIfExists('civil_statuses');
        Schema::dropIfExists('genders');
    }
};
