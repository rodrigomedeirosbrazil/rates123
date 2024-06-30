<?php

namespace App\Managers;

use App\DTOs\OccupancyDiffDTO;
use App\DTOs\OccupancyNotificationDTO;
use App\Models\Occupancy;
use App\Models\User;
use App\Models\UserProperty;
use App\Scraper\DTOs\OccupancyDTO;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class OccupancyManager
{
    public function buildOccupancyNotifications(User $user): ?Collection
    {
        return $user->userProperties->map(function (UserProperty $userProperty) {
            $occupancyNotifications = $this->processOccupancyChange(propertyId: $userProperty->property->id);

            if ($occupancyNotifications->isEmpty()) {
                return null;
            }

            return $occupancyNotifications
                ->map(
                    fn (OccupancyNotificationDTO $occupancyNotifications) => $occupancyNotifications->checkin->translatedFormat('l, d F y')
                        . ': '
                        . number_format($occupancyNotifications->occupancyDiffDTO->oldOccupancy, 0) . '%'
                        . ' -> '
                        . number_format($occupancyNotifications->occupancyDiffDTO->newOccupancy, 0) . '%'
                        . PHP_EOL
                )
                ->push(PHP_EOL)
                ->prepend("{$userProperty->property->name}: " . PHP_EOL)
                ->implode('');
        })
            ->filter()
            ->flatten();
    }

    public function processOccupancyChange(
        int $propertyId,
        CarbonInterface $fromDate = null,
        CarbonInterface $toDate = null
    ): Collection {
        $fromDate = $fromDate ?? now();
        $toDate = $toDate ?? now()->addMonths(6);

        $dates = [];

        while ($fromDate->lessThanOrEqualTo($toDate)) {
            $dates[] = $fromDate->copy();
            $fromDate->addDay();
        }

        return collect($dates)
            ->map(function (CarbonInterface $date) use ($propertyId) {
                $occupancy = $this->checkOccupancyDate($propertyId, $date);

                return $occupancy ? new OccupancyNotificationDTO(
                    checkin: $date,
                    occupancyDiffDTO: $occupancy
                )
                : null;
            })
            ->filter();
    }

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

        if (! $occupancies[0]->updated_at->isToday()) {
            return null;
        }

        $newOccupancy = (int) $occupancies[0]->occupancyPercent;
        $oldOccupancy = (int) $occupancies[1]->occupancyPercent;

        return $this->shouldNotifyOccupancyChange(
            oldOccupancy: $oldOccupancy,
            newOccupancy: $newOccupancy,
        ) ? new OccupancyDiffDTO(
            checkin: $date,
            oldOccupancy: $oldOccupancy,
            newOccupancy: $newOccupancy,
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

    public function processOccupancy(int $propertyId, Collection $occupancies)
    {
        $occupancies->each(
            function (OccupancyDTO $occupancy) use ($propertyId) {
                $occupancyModel = Occupancy::query()
                    ->where('property_id', $propertyId)
                    ->whereDate('checkin', $occupancy->checkin)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if (
                    $occupancyModel
                    && $occupancyModel->total_rooms === $occupancy->totalRooms
                    && $occupancyModel->occupied_rooms === $occupancy->occupiedRooms
                ) {
                    $occupancyModel->updated_at = now();
                    $occupancyModel->save();

                    return $occupancyModel;
                }

                return Occupancy::create([
                    'property_id' => $propertyId,
                    'checkin' => $occupancy->checkin,
                    'total_rooms' => $occupancy->totalRooms,
                    'occupied_rooms' => $occupancy->occupiedRooms,
                ]);
            }
        );
    }
}
