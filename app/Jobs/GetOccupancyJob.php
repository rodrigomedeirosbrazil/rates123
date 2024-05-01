<?php

namespace App\Jobs;

use App\Models\Property;
use App\Models\Occupancy;
use App\Scraper\DTOs\OccupancyDTO;
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

        $occupancies->each(
            function (OccupancyDTO $occupancy) {
                $occupancyModel = Occupancy::query()
                    ->where('property_id', $this->propertyId)
                    ->whereDate('checkin', $occupancy->checkin)
                    ->whereDate('created_at', now()->startOfDay())
                    ->first();

                if (! $occupancyModel) {
                    return Occupancy::create([
                        'property_id' => $this->propertyId,
                        'checkin' => $occupancy->checkin,
                        'total_rooms' => $occupancy->totalRooms,
                        'occupied_rooms' => $occupancy->occupiedRooms,
                    ]);
                }

                $occupancyModel->update([
                    'total_rooms' => $occupancy->totalRooms,
                    'occupied_rooms' => $occupancy->occupiedRooms,
                ]);
            }
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
