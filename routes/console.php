<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Codzienny backup bazy o 03:00 (cron na serwerze)
Illuminate\Support\Facades\Schedule::command('backup:db')->dailyAt('03:00');
