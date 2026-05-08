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
        Schema::create('members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('household_id', 20);
            $table->foreign('household_id')->references('id')->on('households')->cascadeOnDelete();
            $table->string('name', 255)->nullable();            // Computed full name
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->date('birth_date')->index();
            $table->enum('sex', ['M', 'F'])->nullable();         // Original raw value
            $table->string('gender', 20)->nullable();            // male / female / other
            $table->integer('age')->nullable();                  // Computed age at creation
            $table->string('relation', 50)->nullable();
            $table->string('civil_status', 50)->nullable();
            $table->string('education_level', 100)->nullable();
            $table->string('occupation', 100)->nullable();
            $table->boolean('is_pwd')->default(false);
            $table->boolean('is_pregnant')->default(false);
            $table->string('special_needs', 50)->nullable();     // pwd / senior / child / adult
            $table->boolean('is_graduate')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
