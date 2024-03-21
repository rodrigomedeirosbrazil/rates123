<?php

namespace App\Jobs;

use App\Enums\SyncStatusEnum;
use App\Managers\ScrapManager;
use App\Models\MonitoredData;
use App\Models\MonitoredProperty;
use App\Models\MonitoredSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetMonitoredPropertyDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public function __construct(
        public int $monitoredPropertyId,
        public string $propertyName,
        public string $platformSlug,
    ) {
    }

    public function handle(ScrapManager $scrapManager): void
    {
        if (MonitoredSync::propertyIsSyncedToday($this->monitoredPropertyId)) {
            return;
        }

        $sync = MonitoredSync::create([
            'monitored_property_id' => $this->monitoredPropertyId,
            'status' => SyncStatusEnum::InProgress,
            'prices_count' => 0,
            'started_at' => now(),
            'finished_at' => null,
        ]);

        $propertyDTO = MonitoredProperty::with('platform')
            ->findOrFail($this->monitoredPropertyId)
            ->toPropertyDTO();

        try {
            $prices = $propertyDTO->platformSlug === 'booking'
                ? $scrapManager->getPrices($propertyDTO, now()->addDay(), config('platforms.booking.scrap_days'))
                : $scrapManager->getPrices($propertyDTO, now()->addDay(), config('platforms.airbnb.scrap_days'));
        } catch (\Exception $e) {
            $sync->status = SyncStatusEnum::Failed;
            $sync->finished_at = now();
            $sync->exception = $e->getMessage();
            $sync->save();

            return;
        }

        $prices->each(function ($price) {
            MonitoredData::create([
                'monitored_property_id' => $this->monitoredPropertyId,
                'price' => $price->price,
                'checkin' => $price->checkin,
                'available' => $price->available,
                'extra' => $price->extra ?? '[]',
            ]);
        });

        $sync->status = $prices->count() > 0 ? SyncStatusEnum::Successful : SyncStatusEnum::Failed;
        $sync->finished_at = now();
        $sync->prices_count = $prices->count();
        $sync->save();

        if (! $sync->status === SyncStatusEnum::Successful) {
            return;
        }

        dispatch(
            new CheckPropertyPricesJob(
                monitoredPropertyId: $this->monitoredPropertyId,
                propertyName: $this->propertyName,
                platformSlug: $this->platformSlug,
            )
        );
    }

    public function tags(): array
    {
        return [
            'platform: ' . $this->platformSlug,
            'property: ' . $this->propertyName,
            'propertyId: ' . $this->monitoredPropertyId,
        ];
    }
}
