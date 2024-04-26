<?php

namespace App\Console\Commands;

use App\Enums\SyncStatusEnum;
use App\Jobs\CheckPropertyPricesJob;
use App\Managers\ScrapManager;
use App\Models\Rate;
use App\Models\Property;
use App\Models\MonitoredSync;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;

class CapturePricesFromPropertyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'property:capture-prices
                            {propertyId : The property ID}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap and store data from a property';

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

        if (
            MonitoredSync::propertyIsSyncedToday($propertyId)
            && ! confirm('This property has already been synced sucessful today. Do you want to continue?', false)
        ) {
            return 0;
        }

        $propertyDTO = $property->toPropertyDTO();

        $scrapManager = new ScrapManager();

        $startTimestamp = now();

        $sync = MonitoredSync::create([
            'property_id' => $propertyId,
            'status' => SyncStatusEnum::InProgress,
            'prices_count' => 0,
            'started_at' => now(),
            'finished_at' => null,
        ]);

        try {
            $prices = $propertyDTO->platformSlug === 'booking'
                ? $scrapManager->getPrices($propertyDTO, now()->addDay(), config('platforms.booking.scrap_days'))
                : $scrapManager->getPrices($propertyDTO, now()->addDay(), config('platforms.airbnb.scrap_days'));
        } catch (\Exception $e) {
            $sync->status = SyncStatusEnum::Failed;
            $sync->finished_at = now();
            $sync->exception = $e->getMessage();
            $sync->save();

            $this->error($e->getMessage());

            return 1;
        }

        $prices->each(
            fn ($price) => Rate::create([
                'property_id' => $propertyId,
                'price' => $price->price,
                'checkin' => $price->checkin,
                'available' => $price->available,
                'extra' => $price->extra ?? '[]',
            ])
        );

        $sync->status = $prices->count() > 0 ? SyncStatusEnum::Successful : SyncStatusEnum::Failed;
        $sync->finished_at = now();
        $sync->prices_count = $prices->count();
        $sync->save();

        if (! $sync->status === SyncStatusEnum::Failed) {
            $this->error('Sync failed');

            return 1;
        }

        $this->info('Dispatching CheckPropertyPricesJob...');
        dispatch(
            new CheckPropertyPricesJob(
                monitoredPropertyId: $propertyId,
                propertyName: $propertyDTO->name,
                platformSlug: $propertyDTO->platformSlug,
            )
        );

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
