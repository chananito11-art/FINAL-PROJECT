<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-expire pending_payment bookings every 15 minutes
Schedule::command('bookings:expire-holds')->everyFifteenMinutes();

// Check for expired IDs daily
Schedule::command('users:check-id-expiration')->dailyAt('00:01');

// Mark no-shows daily at 1:00 AM (checks if pickup date passed)
Schedule::command('bookings:check-no-shows')->dailyAt('01:00');

// Generate maintenance alerts daily
Schedule::command('maintenance:generate-tasks')->daily();
