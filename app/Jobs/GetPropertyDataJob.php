<?php

namespace App\Jobs;

use App\Enums\SyncStatusEnum;
use App\Managers\CheckPriceManager;
use App\Managers\ScrapManager;
use App\Models\Property;
use App\Models\Sync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetPropertyDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public function __construct(
        public int $propertyId,
        public string $propertyName,
        public string $platformSlug,
    ) {
    }

    public function handle(ScrapManager $scrapManager): void
    {
        $sync = Sync::query()
            ->wherePropertyId($this->propertyId)
            ->whereDate('started_at', today()->addDay())
            ->first();

        if (! $sync) {
            $sync = Sync::create([
                'property_id' => $this->propertyId,
                'status' => SyncStatusEnum::InProgress,
                'prices_count' => 0,
                'started_at' => now()->addDay(),
                'finished_at' => null,
            ]);
        }

        if ($sync->status === SyncStatusEnum::Successful) {
            return;
        }

        $propertyDTO = Property::with('platform')
            ->findOrFail($this->propertyId)
            ->toPropertyDTO();


        $daysBySession = $propertyDTO->platformSlug === 'booking'
            ? config('platforms.booking.scrap_days_session')
            : config('platforms.airbnb.scrap_days_session');

        $days = $propertyDTO->platformSlug === 'booking'
            ? config('platforms.booking.scrap_days')
            : config('platforms.airbnb.scrap_days');

        $lastSyncDays = $sync->prices_count;

        try {
            $prices = $scrapManager->getPrices(
                $propertyDTO,
                now()->addDays($lastSyncDays + 1),
                $daysBySession
            );
        } catch (\Exception $e) {
            $sync->status = SyncStatusEnum::Failed;
            $sync->finished_at = now();
            $sync->exception = $e->getMessage();
            $sync->save();

            return;
        }

        (new CheckPriceManager())->processPrices($this->propertyId, $prices);

        if (
            ($prices->count() + $lastSyncDays) < $days
            && $prices->count() === $daysBySession
        ) {
            $sync->status = SyncStatusEnum::InProgress;
            $sync->prices_count += $prices->count();
            $sync->save();

            dispatch(
                new GetPropertyDataJob(
                    propertyId: $this->propertyId,
                    propertyName: $this->propertyName,
                    platformSlug: $this->platformSlug,
                )
            );

            return;
        }

        $sync->status = $prices->count() > 0 ? SyncStatusEnum::Successful : SyncStatusEnum::Failed;
        $sync->finished_at = now();
        $sync->prices_count += $prices->count();
        $sync->save();

        if (! $sync->status === SyncStatusEnum::Successful) {
            return;
        }

        dispatch(
            new CheckPropertyPricesJob(
                propertyId: $this->propertyId,
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
            'propertyId: ' . $this->propertyId,
        ];
    }
}
