<?php

namespace App\Console\Commands;

use App\Managers\ScrapManager;
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

                $prices->map(function ($price) use ($property) {
                    return [
                        'monitored_property_id' => $property->id,
                        'price' => $this->humanReadableSizeToInt(data_get($price, 'avgPriceFormatted')),
                        'checkin' => data_get($price, 'checkin'),
                        'available' => data_get($price, 'available'),
                    ];
                });
            });
    }

    public function humanReadableSizeToInt(string $value): int
    {
        $number = (float) preg_replace('/[^0-9\.]/', '', $value);

        $prefix = strtolower(preg_replace('/[^tgmk]/i', '', $value));

        switch ($prefix) {
            case 't': $number *= 1000;
                // no break
            case 'g': $number *= 1000;
                // no break
            case 'm': $number *= 1000;
                // no break
            case 'k': $number *= 1000;
        }

        return (int) $number;
    }
}
