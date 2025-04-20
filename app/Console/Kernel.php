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
        $schedule->command('reset:attendance')
            ->sundays()
            ->timezone('Asia/Manila');
    }
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // Load all Artisan commands in this directory
        $this->load(__DIR__.'/Commands');

        // Include the routes/console.php file if needed
        require base_path('routes/console.php');
    }
}