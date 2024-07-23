<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        if (! app()->isProduction()) {
            return;
        }

        $schedule->command('app:monitore')->dailyAt('12:00');
        $schedule->command('app:send-prices')->dailyAt('15:00');
        $schedule->command('app:occupancy')->twiceDaily(12, 18);
        $schedule->command('app:send-occupancies')->twiceDaily(12, 18, 10);
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
