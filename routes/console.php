<?php

use App\Models\BackupSchedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automatic Database Backups (Controlled via Web UI)
Schedule::command('app:backup-db')->everyMinute()->when(function () {
    $schedule = BackupSchedule::first();
    if (!$schedule || !$schedule->enabled) {
        return false;
    }

    $now = now();

    // Check frequency constraints
    if ($schedule->frequency === 'weekly' && $now->dayOfWeek !== ($schedule->day_of_week ?? 1)) {
        return false;
    }
    if ($schedule->frequency === 'monthly' && $now->day !== ($schedule->day_of_month ?? 1)) {
        return false;
    }

    // Compare current hour & minute (HH:MM)
    return $now->format('H:i') === ($schedule->time ?? '02:00');
})->runInBackground();
