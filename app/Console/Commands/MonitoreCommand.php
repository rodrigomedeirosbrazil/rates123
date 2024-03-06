<?php

namespace App\Console\Commands;

use App\Jobs\GetMonitoredPropertyDataJob;
use App\Models\MonitoredProperty;
use App\Models\MonitoredSync;
use Illuminate\Console\Command;

class MonitoreCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:monitore';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap data from monitored properties';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        MonitoredProperty::all()
            ->each(
                function (MonitoredProperty $property, int $index) {
                    $isSyncedSuccesfulToday = MonitoredSync::query()
                        ->whereMonitoredPropertyId($property->id)
                        ->whereDate('started_at', now())
                        ->whereSuccessful(true)
                        ->exists();

                    if ($isSyncedSuccesfulToday) {
                        return;
                    }

                    dispatch(
                        new GetMonitoredPropertyDataJob($property->id)
                    )
                        ->delay(now()->addMinutes($index * 1));
                }
            );
    }
}
