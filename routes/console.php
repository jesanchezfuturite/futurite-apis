<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Schedule::command('googleads:list-customers')->hourly();
Schedule::command('googleads:list-campaigns')->hourlyAt(10);
Schedule::command('googleads:update-indicators')->everyTwoHours(20);

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
