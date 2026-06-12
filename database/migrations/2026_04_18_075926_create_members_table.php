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
        Schema::create('household_members', function (Blueprint $table) {
            $table->string('member_id', 255)->primary();
            $table->string('household_id', 255);
            $table->string('name', 255)->nullable();
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->date('birth_date');
            $table->unsignedInteger('age')->nullable();
            
            // Normalized lookup IDs
            $table->unsignedInteger('gender_id')->nullable();
            $table->unsignedInteger('relationship_id')->nullable();
            $table->unsignedInteger('civil_status_id')->nullable();
            $table->unsignedInteger('education_level_id')->nullable();
            $table->unsignedInteger('occupation')->nullable();
            
            $table->string('special_needs', 50)->nullable();
            $table->boolean('is_graduate')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('household_id')->references('household_id')->on('households')->cascadeOnDelete();
            
            $table->index('household_id');
            $table->index('birth_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('household_members');
    }
};
