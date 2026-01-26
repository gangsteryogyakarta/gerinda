<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Process scheduled WhatsApp campaigns every minute
Schedule::command('whatsapp:process-campaigns')->everyMinute();

// Send H-1 reminders and H+1 thank you messages at 8 AM daily
Schedule::command('whatsapp:event-reminders')->dailyAt('08:00');
