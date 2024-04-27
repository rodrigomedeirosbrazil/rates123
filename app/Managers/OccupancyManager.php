<?php

namespace App\Managers;

use App\DTOs\OccupancyDiffDTO;
use App\Models\Occupancy;
use Carbon\CarbonInterface;

class OccupancyManager
{
    public function checkOccupancyDate(int $propertyId, CarbonInterface $date): ?OccupancyDiffDTO
    {
        $occupancies = Occupancy::query()
            ->where('property_id', $propertyId)
            ->whereDate('checkin', $date)
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();

        if ($occupancies->count() < 2) {
            return null;
        }

        return $this->shouldNotifyOccupancyChange(
            oldOccupancy: $occupancies[1]->occupancyPercent(),
            newOccupancy: $occupancies[0]->occupancyPercent(),
        ) ? new OccupancyDiffDTO(
            checkin: $date,
            oldOccupancy: $occupancies[1]->occupancyPercent(),
            newOccupancy: $occupancies[0]->occupancyPercent(),
        ) : null;
    }

    public function shouldNotifyOccupancyChange(int $oldOccupancy, int $newOccupancy): bool
    {
        if ($oldOccupancy >= $newOccupancy) {
            return false;
        }

        if (
            ! $this->is10Multiple($newOccupancy)
            && ! $this->newNumberIsMoreThan10PointsDifference($newOccupancy, $oldOccupancy)
            && ! $this->newNumberPass10MultipleOfOldNumber($newOccupancy, $oldOccupancy)
        ) {
            return false;
        }

        return true;
    }

    private function is10Multiple(int $number): bool
    {
        return $number % 10 === 0;
    }

    private function newNumberIsMoreThan10PointsDifference(int $newNumber, int $oldNumber): bool
    {
        return $newNumber - $oldNumber >= 10;
    }

    private function newNumberPass10MultipleOfOldNumber(int $newNumber, int $oldNumber): bool
    {
        $newNumberMod = $newNumber % 10;
        $oldNumberMod = $oldNumber % 10;

        return $newNumber > $oldNumber
            && $oldNumberMod > $newNumberMod;
    }
}
