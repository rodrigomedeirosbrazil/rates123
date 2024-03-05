<?php

namespace App\Jobs;

use App\Managers\ScrapManager;
use App\Models\MonitoredData;
use App\Models\MonitoredProperty;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetMonitoredPropertyDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $monitoredPropertyId
    ) {
    }

    public function handle(ScrapManager $scrapManager): void
    {
        $property = MonitoredProperty::findOrFail($this->monitoredPropertyId);

        $scrapManager->getPrices($property->url, $property->capture_months_number)
            ->each(
                fn ($price) => MonitoredData::create([
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
            );
    }
}
