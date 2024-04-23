<?php

namespace App\Console\Commands;

use App\Jobs\GetOccupancyJob;
use App\Models\MonitoredProperty;
use Illuminate\Console\Command;

class GetOccupancyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:occupancy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap occupancy from monitored properties';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        MonitoredProperty::query()
            ->whereNotNull('hits_property_name')
            ->cursor()
            ->each(
                function (MonitoredProperty $property) {
                    $this->info("Dispatching job to get occupancy for {$property->name}");
                    dispatch(
                        new GetOccupancyJob(
                            monitoredPropertyId: $property->id,
                            propertyName: $property->name,
                            platformSlug: $property->platform->slug,
                        )
                    );
                }
            );
    }
}