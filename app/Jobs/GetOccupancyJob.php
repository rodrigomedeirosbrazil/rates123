<?php

namespace App\Jobs;

use App\Managers\OccupancyManager;
use App\Models\Property;
use App\Scraper\HitsScraper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetOccupancyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public function __construct(
        public int $propertyId,
        public string $propertyName,
        public string $platformSlug,
    ) {
    }

    public function handle(HitsScraper $hitsScraper): void
    {
        $property = Property::findOrFail($this->propertyId);

        if (! $property->hits_property_id) {
            return;
        }

        $occupancies = $hitsScraper->getOccupancies(
            $property->hits_property_id,
            now(),
            now()->addMonths(6)->endOfMonth()
        );

        (new OccupancyManager())->processOccupancy(
            $this->propertyId,
            $occupancies
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
