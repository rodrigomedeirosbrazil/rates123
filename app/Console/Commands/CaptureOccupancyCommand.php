<?php

namespace App\Console\Commands;

use App\Models\Occupancy;
use App\Scraper\DTOs\OccupancyDTO;
use App\Scraper\HitsScraper;
use Illuminate\Console\Command;

class CaptureOccupancyCommand extends Command
{
    protected $signature = 'property:capture-occupancy';
    protected $description = 'Scrap and store occupancy data from a property';

    public function handle()
    {
        $propertyId = 2;
        $hitsScraper = new HitsScraper();

        $startTimestamp = now();

        try {
            $occupancies = $hitsScraper->getOccupancies(
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
                    ->where('monitored_property_id', $propertyId)
                    ->whereDate('checkin', $occupancy->checkin)
                    ->whereDate('created_at', now()->startOfDay())
                    ->first();

                if (! $occupancyModel) {
                    return Occupancy::create([
                        'monitored_property_id' => $propertyId,
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
