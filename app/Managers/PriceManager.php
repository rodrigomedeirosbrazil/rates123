<?php

namespace App\Managers;

use App\Models\MonitoredData;
use App\Models\MonitoredProperty;
use App\Models\PriceNotification;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class PriceManager
{
    public function calculatePropertyModePrice(int $propertyId)
    {
        $property = MonitoredProperty::findOrFail($propertyId);

        return $property->priceDatas
            ->filter(fn ($price) => $price->available === true)
            ->where(fn ($price) => $price->checkin > now()->subYear())
            ->mode('price')[0];
    }

    public function calculateCheckinPropertyModePrice(int $propertyId, CarbonInterface $checkin)
    {
        return MonitoredData::query()
            ->where('monitored_property_id', $propertyId)
            ->where('checkin', $checkin)
            ->where('available', true)
            ->get()
            ->mode('price')[0];
    }

    public function getUserPriceNotificationsByCreatedAt(int | User $user, CarbonInterface $createdAt = null): Collection
    {
        if (is_int($user)) {
            $userModel = User::findOrFail($user);
        } else {
            $userModel = $user;
        }

        $followedPropertyIds = $userModel->properties->pluck('id');

        if ($followedPropertyIds->isEmpty()) {
            return collect();
        }

        $searchDate = $createdAt ?? now();

        return PriceNotification::query()
            ->whereDate('created_at', $searchDate)
            ->whereIn('monitored_property_id', $followedPropertyIds)
            ->orderBy('checkin', 'asc')
            ->get();
    }
}
