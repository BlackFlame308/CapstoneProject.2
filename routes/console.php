<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use App\Services\DashboardService;

Schedule::call(function () {
    app(DashboardService::class)->refreshAnalytics();
})->dailyAt('00:00')->name('refresh-analytics')->withoutOverlapping();
