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
use Illuminate\Support\Facades\Validator;

class GetMonitoredPropertyDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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

        $prices = $scrapManager->getPrices($property->url, $property->capture_months_number);

        $sync->prices_count = count($prices);
        $sync->save();

        $prices
            ->map(fn ($price) => [
                'monitored_property_id' => $property->id,
                'price' => human_readable_size_to_int(
                    data_get($price, 'avgPriceFormatted') ?? '0'
                ),
                'checkin' => data_get($price, 'checkin'),
                'available' => data_get($price, 'available') ?? false,
                'extra' => [
                    'minLengthOfStay' => data_get($price, 'minLengthOfStay'),
                ],
            ])
            ->filter(fn ($price) => $this->validator($price))
            ->each(
                fn ($price) => MonitoredData::create($price)
            );

        $sync->successful = true;
        $sync->finished_at = now();
        $sync->save();
    }

    public function validator(array $price): bool
    {
        $validator = Validator::make($price, [
            'monitored_property_id' => 'required|numeric',
            'price' => 'required|numeric',
            'checkin' => 'required|date',
            'available' => 'required|boolean',
        ]);

        return ! $validator->fails();
    }
}
