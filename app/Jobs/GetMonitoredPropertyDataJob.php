<?php

namespace App\Jobs;

use App\Managers\ScrapManager;
use App\Models\MonitoredData;
use App\Models\MonitoredProperty;
use App\Models\MonitoredSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class GetMonitoredPropertyDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 10;

    public function __construct(
        public int $monitoredPropertyId
    ) {
    }

    public function handle(ScrapManager $scrapManager): void
    {
        $sync = MonitoredSync::create([
            'monitored_property_id' => $this->monitoredPropertyId,
            'successful' => false,
            'prices_count' => 0,
            'started_at' => now(),
            'finished_at' => null,
        ]);

        $property = MonitoredProperty::findOrFail($this->monitoredPropertyId);

        $prices = $property->platform->slug === 'booking'
            ? $scrapManager->getBookingPrices($property->url, $property->capture_months_number)
            : $scrapManager->getAirbnbPrices($property->url, now()->addDay(), 15);

        $sync->prices_count = count($prices);
        $sync->save();

        $importedPrices = $property->platform->slug === 'booking'
            ? $this->importBookingPrices($prices, $property->id)
            : $this->importAirbnbPrices($prices, $property->id);

        $sync->successful = $importedPrices > 0;
        $sync->finished_at = now();
        $sync->prices_count = $importedPrices;
        $sync->save();

        if (! $sync->successful) {
            $this->release(now()->addMinutes(15));
        }
    }

    public function importBookingPrices(Collection $prices, int $propertyId): int
    {
        return $prices
            ->map(fn ($price) => [
                'monitored_property_id' => $propertyId,
                'price' => human_readable_size_to_int(
                    data_get($price, 'avgPriceFormatted') ?? '0'
                ),
                'checkin' => data_get($price, 'checkin'),
                'available' => data_get($price, 'available') ?? false,
                'extra' => [
                    'minLengthOfStay' => data_get($price, 'minLengthOfStay'),
                ],
            ])
            ->filter(fn ($price) => $this->bookingValidator($price))
            ->each(
                fn ($price) => MonitoredData::create($price)
            )->count();
    }

    public function importAirbnbPrices(Collection $prices, int $propertyId): int
    {
        return $prices
            ->map(fn ($price) => [
                'monitored_property_id' => $propertyId,
                'price' => data_get($price, 'price') ?? '0',
                'checkin' => data_get($price, 'checkin'),
                'available' => data_get($price, 'available') ?? false,
                'extra' => [],
            ])
            ->each(
                fn ($price) => MonitoredData::create($price)
            )->count();
    }

    public function bookingValidator(array $price): bool
    {
        $validator = Validator::make($price, [
            'monitored_property_id' => 'required|numeric',
            'price' => 'required|numeric',
            'checkin' => 'required|date',
            'available' => 'required|boolean',
        ]);

        if (! $validator->fails()) {
            return true;
        }

        Log::warning('Invalid price data', $validator->errors()->toArray());

        return false;
    }
}
