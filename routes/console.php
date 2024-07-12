<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Schedule::command('googleads:list-customers')->everySixHours(0);;
Schedule::command('googleads:list-campaigns')->everySixHours(10);;
Schedule::command('googleads:update-indicators')->everyThreeHours(20);

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
