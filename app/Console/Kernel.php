<?php

namespace App\Console;

use App\Jobs\SendPriceNotificationEmailJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('app:monitore')->dailyAt('12:00');
        $schedule->job(SendPriceNotificationEmailJob::class)->dailyAt('15:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
