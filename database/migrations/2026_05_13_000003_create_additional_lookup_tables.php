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
        // Severity Levels
        Schema::create('severity_levels', function (Blueprint $table) {
            $table->increments('severity_id');
            $table->string('severity_key', 50)->unique();
            $table->string('severity_label', 100);
        });

        // Household Statuses
        Schema::create('household_statuses', function (Blueprint $table) {
            $table->increments('status_id');
            $table->string('status_key', 50)->unique();
            $table->string('status_label', 100);
        });

        // Accommodation Types
        Schema::create('accommodation_types', function (Blueprint $table) {
            $table->increments('type_id');
            $table->string('type_key', 50)->unique();
            $table->string('type_label', 100);
        });

        // Urgency Levels
        Schema::create('urgency_levels', function (Blueprint $table) {
            $table->increments('urgency_id');
            $table->string('urgency_key', 50)->unique();
            $table->string('urgency_label', 100);
        });

        // Recurrence Types
        Schema::create('recurrence_types', function (Blueprint $table) {
            $table->increments('type_id');
            $table->string('type_key', 50)->unique();
            $table->string('type_label', 100);
        });

        // Notification Channels
        Schema::create('notification_channels', function (Blueprint $table) {
            $table->increments('channel_id');
            $table->string('channel_key', 50)->unique();
            $table->string('channel_label', 100);
        });

        // Notification Statuses
        Schema::create('notification_statuses', function (Blueprint $table) {
            $table->increments('status_id');
            $table->string('status_key', 50)->unique();
            $table->string('status_label', 100);
        });

        // Analytics Job Status
        Schema::create('analytics_job_status', function (Blueprint $table) {
            $table->increments('status_id');
            $table->string('status_key', 50)->unique();
            $table->string('status_label', 100);
        });

        // Center Issue Categories
        Schema::create('center_issue_categories', function (Blueprint $table) {
            $table->increments('category_id');
            $table->string('category_key', 50)->unique();
            $table->string('category_label', 100);
        });

        // Center Issue Report Statuses
        Schema::create('center_issue_report_statuses', function (Blueprint $table) {
            $table->increments('status_id');
            $table->string('status_key', 50)->unique();
            $table->string('status_label', 100);
        });

        // Resource Request Status
        Schema::create('resource_request_status', function (Blueprint $table) {
            $table->increments('status_id');
            $table->string('status_key', 50)->unique();
            $table->string('status_label', 100);
        });

        // Field Report Categories
        Schema::create('field_report_categories', function (Blueprint $table) {
            $table->increments('category_id');
            $table->string('category_key', 50)->unique();
            $table->string('category_label', 100);
        });

        // Rescue Teams
        Schema::create('rescue_teams', function (Blueprint $table) {
            $table->increments('team_id');
            $table->string('team_name', 100);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rescue_teams');
        Schema::dropIfExists('field_report_categories');
        Schema::dropIfExists('resource_request_status');
        Schema::dropIfExists('center_issue_report_statuses');
        Schema::dropIfExists('center_issue_categories');
        Schema::dropIfExists('analytics_job_status');
        Schema::dropIfExists('notification_statuses');
        Schema::dropIfExists('notification_channels');
        Schema::dropIfExists('recurrence_types');
        Schema::dropIfExists('urgency_levels');
        Schema::dropIfExists('accommodation_types');
        Schema::dropIfExists('household_statuses');
        Schema::dropIfExists('severity_levels');
    }
};
