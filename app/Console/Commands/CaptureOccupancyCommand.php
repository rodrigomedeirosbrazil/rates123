<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\Occupancy;
use App\Scraper\DTOs\OccupancyDTO;
use App\Scraper\HitsScraper;
use Illuminate\Console\Command;

class CaptureOccupancyCommand extends Command
{
    protected $signature = 'property:capture-occupancy
                            {propertyId : The property ID}';

    protected $description = 'Scrap and store occupancy data from a property';

    public function handle()
    {
        $propertyId = $this->argument('propertyId');
        $property = Property::find($propertyId);

        if (! $property) {
            $this->error("Couldn't find a property with ID {$propertyId}");

            return 1;
        }

        if (! $property->hits_property_name) {
            $this->error("Property name not available for property with ID {$propertyId}");

            return 1;
        }

        $hitsScraper = new HitsScraper();

        $startTimestamp = now();

        try {
            $occupancies = $hitsScraper->getOccupancies(
                $property->hits_property_name,
                now(),
                now()->addMonths(6)->endOfMonth()
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return 1;
        }

        $occupancies->each(
            function (OccupancyDTO $occupancy) use ($propertyId) {
                $occupancyModel = Occupancy::query()
                    ->where('property_id', $propertyId)
                    ->whereDate('checkin', $occupancy->checkin)
                    ->whereDate('created_at', now()->startOfDay())
                    ->first();

                if (! $occupancyModel) {
                    return Occupancy::create([
                        'property_id' => $propertyId,
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

        $this->line('Started at: ' . $startTimestamp->toDateTimeString());
        $this->line('Finished at: ' . now()->toDateTimeString());
        $this->line('Elapsed seconds: ' . $startTimestamp->diffInSeconds(now()));
    }
}
