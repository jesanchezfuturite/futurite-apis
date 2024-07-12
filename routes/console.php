<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Schedule::command('googleads:list-customers')->everyFourHours(0);;
Schedule::command('googleads:list-campaigns')->everyFourHours(10);;
Schedule::command('googleads:update-indicators')->everyThreeHours(20);

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
