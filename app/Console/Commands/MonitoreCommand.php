<?php

namespace App\Console\Commands;

use App\Managers\ScrapManager;
use App\Models\MonitoredData;
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
        $scrapManager = new ScrapManager();

        MonitoredProperty::all()
            ->each(function (MonitoredProperty $property) use ($scrapManager) {
                $prices = $scrapManager->getPrices($property->url, 2);

                $this->info("Scrapped {$prices->count()} prices from {$property->name}");

                $prices->each(function ($price) use ($property) {
                    MonitoredData::create([
                        'monitored_property_id' => $property->id,
                        'price' => human_readable_size_to_int(data_get($price, 'avgPriceFormatted') ?? '0'),
                        'checkin' => data_get($price, 'checkin'),
                        'available' => data_get($price, 'available') ?? false,
                        'extra' => [
                            'minLengthOfStay' => data_get($price, 'minLengthOfStay'),
                        ],
                    ]);
                });
            });
    }
}
