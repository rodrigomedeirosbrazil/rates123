<?php

namespace App\Console\Commands;

use App\Managers\ScrapManager;
use App\Models\Property;
use Illuminate\Console\Command;

class GetPricesFromPropertyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'property:get-prices
                            {propertyId : The property ID}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap data from a property';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $propertyId = $this->argument('propertyId');
        $property = Property::find($propertyId);

        if (! $property) {
            $this->error("Couldn't find a property with ID {$propertyId}");

            return 1;
        }

        $propertyDTO = $property->toPropertyDTO();

        $scrapManager = new ScrapManager();

        $startTimestamp = now();
        $prices = $propertyDTO->platformSlug === 'booking'
                ? $scrapManager->getPrices($propertyDTO, now()->addDay(), config('platforms.booking.scrap_days'))
                : $scrapManager->getPrices($propertyDTO, now()->addDay(), config('platforms.airbnb.scrap_days'));

        $this->table(['Check In', 'Price', 'Available', 'Extra'], $prices->map(fn ($price) => [
            $price->checkin->toDateString(),
            $price->price, $price->available,
            json_encode($price->extra),
        ])->toArray());

        $this->line('Started at: ' . $startTimestamp->toDateTimeString());
        $this->line('Finished at: ' . now()->toDateTimeString());
        $this->line('Elapsed seconds: ' . $startTimestamp->diffInSeconds(now()));
    }
}
