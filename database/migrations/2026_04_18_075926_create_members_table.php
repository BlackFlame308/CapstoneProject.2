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
            $table->string('member_id', 255)->primary();
            $table->string('household_id', 255);
            $table->string('name', 255)->nullable();
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->date('birth_date');
            $table->string('sex', 1)->nullable();
            $table->string('gender', 20)->nullable();
            $table->unsignedInteger('age')->nullable();
            $table->string('relation', 50)->nullable();
            $table->string('civil_status', 50)->nullable();
            $table->string('occupation', 100)->nullable();
            $table->string('education_level', 100)->nullable();
            $table->boolean('is_pwd')->default(false);
            $table->boolean('is_senior')->default(false);
            $table->boolean('is_pregnant')->default(false);
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
        Schema::dropIfExists('members');
    }
};
