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
Schema::create('households', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('household_code', 50)->unique()->index();
            $table->string('household_name', 100);
            $table->string('email', 150)->nullable()->unique();
            $table->integer('member_count')->default(0);
            $table->foreignUuid('address_id')->constrained('addresses')->cascadeOnDelete();
            $table->string('contact_number', 50)->nullable()->index();
            $table->string('emergency_contact', 50)->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('households');
    }
};
