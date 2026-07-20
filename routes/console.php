<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$schedule = config('erp.sync.schedule');

if (filled($schedule)) {
    Schedule::command('erp:sync-catalog')->cron($schedule);
}