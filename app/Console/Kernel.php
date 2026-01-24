<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Reset queues once between 6:00 PM and 7:00 PM in app timezone
        // Runs every 5 minutes during the window; command enforces once-per-day
        $schedule->command('queue:reset-system')
            ->everyFiveMinutes()
            ->between('18:00', '19:00')
            ->timezone(config('app.timezone'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
