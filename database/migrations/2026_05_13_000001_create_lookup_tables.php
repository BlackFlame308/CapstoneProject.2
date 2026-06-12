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
        // Genders
        Schema::create('genders', function (Blueprint $table) {
            $table->increments('gender_id');
            $table->string('gender_key', 20)->unique();
            $table->string('gender_label', 20);
        });

        DB::table('genders')->insert([
            ['gender_id' => 1, 'gender_key' => 'male', 'gender_label' => 'Male'],
            ['gender_id' => 2, 'gender_key' => 'female', 'gender_label' => 'Female'],
            ['gender_id' => 3, 'gender_key' => 'other', 'gender_label' => 'Other'],
        ]);

        // Civil Statuses
        Schema::create('civil_statuses', function (Blueprint $table) {
            $table->increments('status_id');
            $table->string('status_key', 20)->unique();
            $table->string('status_label', 20);
        });

        DB::table('civil_statuses')->insert([
            ['status_id' => 1, 'status_key' => 'single', 'status_label' => 'Single'],
            ['status_id' => 2, 'status_key' => 'married', 'status_label' => 'Married'],
            ['status_id' => 3, 'status_key' => 'widowed', 'status_label' => 'Widowed'],
            ['status_id' => 4, 'status_key' => 'separated', 'status_label' => 'Separated'],
            ['status_id' => 5, 'status_key' => 'divorced', 'status_label' => 'Divorced'],
            ['status_id' => 6, 'status_key' => 'annulled', 'status_label' => 'Annulled'],
        ]);

        // Education Levels
        Schema::create('education_levels', function (Blueprint $table) {
            $table->increments('education_level_id');
            $table->string('education_level_key', 20)->unique();
            $table->string('education_level_label', 20);
        });

        DB::table('education_levels')->insert([
            ['education_level_id' => 1, 'education_level_key' => 'elementary', 'education_level_label' => 'Elementary'],
            ['education_level_id' => 2, 'education_level_key' => 'high_school', 'education_level_label' => 'High School'],
            ['education_level_id' => 3, 'education_level_key' => 'college', 'education_level_label' => 'College'],
            ['education_level_id' => 4, 'education_level_key' => 'post_graduate', 'education_level_label' => 'Post Graduate'],
        ]);

        // Relationships
        Schema::create('relationships', function (Blueprint $table) {
            $table->increments('relationship_id');
            $table->string('relationship_key', 50)->unique();
            $table->string('relationship_label', 100);
        });

        DB::table('relationships')->insert([
            ['relationship_id' => 1, 'relationship_key' => 'head_of_household', 'relationship_label' => 'Head of Household'],
            ['relationship_id' => 2, 'relationship_key' => 'spouse', 'relationship_label' => 'Spouse'],
            ['relationship_id' => 3, 'relationship_key' => 'child', 'relationship_label' => 'Child'],
            ['relationship_id' => 4, 'relationship_key' => 'parent', 'relationship_label' => 'Parent'],
            ['relationship_id' => 5, 'relationship_key' => 'sibling', 'relationship_label' => 'Sibling'],
            ['relationship_id' => 6, 'relationship_key' => 'other_relative', 'relationship_label' => 'Other Relative'],
        ]);

        // Occupations
        Schema::create('occupations', function (Blueprint $table) {
            $table->increments('occuaption_id');
            $table->string('occupation_name', 100);
        });

        DB::table('occupations')->insert([
            ['occuaption_id' => 1, 'occupation_name' => 'Teacher'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occupations');
        Schema::dropIfExists('relationships');
        Schema::dropIfExists('education_levels');
        Schema::dropIfExists('civil_statuses');
        Schema::dropIfExists('genders');
    }
};
