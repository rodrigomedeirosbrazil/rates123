<?php

namespace App\Managers;

use App\Enums\PriceNotificationTypeEnum;
use App\Models\Rate;
use App\Models\PriceNotification;
use Carbon\CarbonInterface;

class CheckPriceManager
{
    public function checkPropertyPrices(int $propertyId): void
    {
        $hasNotificationToday = PriceNotification::query()
            ->wherePropertyId($propertyId)
            ->whereDate('created_at', now())
            ->exists();

        if ($hasNotificationToday) {
            return;
        }

        $lastPrice = Rate::query()
            ->where('property_id', $propertyId)
            ->orderBy('checkin', 'desc')
            ->firstOrFail();

        $date = now()->addDay();

        while ($lastPrice->checkin->gte($date)) {
            $this->checkPriceDate($propertyId, $date);
            $date->addDay();
        }
    }

    public function checkPriceDate(int $propertyId, CarbonInterface $date): void
    {
        $prices = Rate::query()
            ->where('property_id', $propertyId)
            ->whereDate('checkin', $date)
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();

        if ($prices->count() < 2) {
            return;
        }

        $newPrice = $prices[0];
        $oldPrice = $prices[1];

        if (
            ($newPrice->available
                && $oldPrice->available
                && $newPrice->price === $oldPrice->price)
            || (! $newPrice->available && ! $oldPrice->available)
        ) {
            return;
        }

        if (
            $newPrice->available
            && ! $oldPrice->available
        ) {
            PriceNotification::create([
                'property_id' => $propertyId,
                'checkin' => $date,
                'type' => PriceNotificationTypeEnum::PriceAvailable,
                'before' => 0,
                'after' => $newPrice->price,
                'average_price' => 0,
            ]);

            return;
        }

        if (
            ! $newPrice->available
            && $oldPrice->available
        ) {
            PriceNotification::create([
                'property_id' => $propertyId,
                'checkin' => $date,
                'type' => PriceNotificationTypeEnum::PriceUnavailable,
                'before' => $oldPrice->price,
                'after' => 0,
                'average_price' => 0,
            ]);

            return;
        }

        if ($newPrice->price > $oldPrice->price) {
            PriceNotification::create([
                'property_id' => $propertyId,
                'checkin' => $date,
                'type' => PriceNotificationTypeEnum::PriceUp,
                'before' => $oldPrice->price,
                'after' => $newPrice->price,
                'average_price' => number_format(
                    (new PriceManager())->calculatePropertyModePrice($propertyId),
                    2
                ),
            ]);

            return;
        }

        if ($newPrice->price < $oldPrice->price) {
            PriceNotification::create([
                'property_id' => $propertyId,
                'checkin' => $date,
                'type' => PriceNotificationTypeEnum::PriceDown,
                'before' => $oldPrice->price,
                'after' => $newPrice->price,
                'average_price' => number_format(
                    (new PriceManager())->calculatePropertyModePrice($propertyId),
                    2
                ),
            ]);

            return;
        }
    }
}
