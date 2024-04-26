<?php

namespace App\Console\Commands;

use App\Jobs\CheckPropertyPricesJob;
use App\Models\Property;
use Illuminate\Console\Command;

class CheckPricesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check prices for all monitored properties.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Property::all()
            ->each(
                function (Property $property) {
                    dispatch(
                        new CheckPropertyPricesJob($property->id)
                    );
                }
            );
    }
}
