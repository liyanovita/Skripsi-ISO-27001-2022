<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// CAPA reminder schedule — command logic lives in App\Console\Commands\SendCapaReminders
Schedule::command('capa:send-reminders')
    ->cron(config('notifications.capa_reminders.schedule', '0 8 * * 1-5'))
    ->when(fn() => (bool) config('notifications.enabled', true) && (bool) config('notifications.capa_reminders.enabled', true))
    ->withoutOverlapping();

