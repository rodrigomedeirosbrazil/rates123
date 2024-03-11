<?php

namespace App\Jobs;

use App\Managers\ScrapManager;
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

    public $tries = 10;

    public function __construct(
        public int $monitoredPropertyId,
        public string $propertyName,
        public string $platformSlug,
    ) {
    }

    public function handle(ScrapManager $scrapManager): void
    {
        $isSyncedSuccesfulToday = MonitoredSync::query()
            ->whereMonitoredPropertyId($this->monitoredPropertyId)
            ->whereDate('started_at', now())
            ->whereSuccessful(true)
            ->exists();

        if ($isSyncedSuccesfulToday) {
            return;
        }

        $sync = MonitoredSync::create([
            'monitored_property_id' => $this->monitoredPropertyId,
            'successful' => false,
            'prices_count' => 0,
            'started_at' => now(),
            'finished_at' => null,
        ]);

        $propertyDTO = MonitoredProperty::with('platform')
            ->findOrFail($this->monitoredPropertyId)
            ->toPropertyDTO();

        try {
            $prices = $propertyDTO->platformSlug === 'booking'
                ? $scrapManager->getPrices($propertyDTO, now()->addDay(), 180)
                : $scrapManager->getPrices($propertyDTO, now()->addDay(), 6);
        } catch (\Exception $e) {
            $sync->successful = false;
            $sync->finished_at = now();
            $sync->save();

            $this->release(now()->addMinutes(15));
            // TODO: Save exception to MonitoredSync

            return;
        }

        $sync->successful = $prices->count() > 0;
        $sync->finished_at = now();
        $sync->prices_count = $prices->count();
        $sync->save();

        if (! $sync->successful) {
            $this->release(now()->addMinutes(15));
        }
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
