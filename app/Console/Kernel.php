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
        $schedule->command('airtable:refresh-webhook')->weekly();
        $schedule->command('airtable:fetch-webhooks')->everyMinute();
        $schedule->command('crisp:update')->everyMinute();


        $schedule->command('telescope:prune --hours=140')->daily();


        // Schedule your notification command at 6 AM Pakistan time
        $schedule->command('avg:salary')->timezone('Asia/Karachi')->dailyAt('06:00');

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}