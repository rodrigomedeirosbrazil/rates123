<?php

namespace App\Console\Commands;

use App\Jobs\GetMonitoredPropertyDataJob;
use App\Models\MonitoredProperty;
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
                    dispatch(
                        new GetMonitoredPropertyDataJob(
                            monitoredPropertyId: $property->id,
                            propertyName: $property->name,
                            platformSlug: $property->platform->slug,
                        )
                    )
                        ->delay(now()->addMinutes($index * 1));
                }
            );
    }
}
