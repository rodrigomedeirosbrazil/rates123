<?php

namespace App\Managers;

use App\Enums\PriceNotificationTypeEnum;
use App\Models\MonitoredData;
use App\Models\PriceNotification;
use Carbon\CarbonInterface;

class CheckPriceManager
{
    public function checkPropertyPrices(int $propertyId): void
    {
        $hasNotificationToday = PriceNotification::query()
            ->whereMonitoredPropertyId($propertyId)
            ->whereDate('created_at', now())
            ->exists();

        if ($hasNotificationToday) {
            return;
        }

        $lastPrice = MonitoredData::query()
            ->where('monitored_property_id', $propertyId)
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
        $prices = MonitoredData::query()
            ->where('monitored_property_id', $propertyId)
            ->whereDate('checkin', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($prices->count() < 2) {
            return;
        }

        if (
            ($prices[0]->available
                && $prices[1]->available
                && $prices[0]->price === $prices[1]->price)
            || (! $prices[0]->available && ! $prices[1]->available)
        ) {
            return;
        }

        if (
            $prices[0]->available
            && ! $prices[1]->available
        ) {
            PriceNotification::create([
                'monitored_property_id' => $propertyId,
                'checkin' => $date,
                'type' => PriceNotificationTypeEnum::PriceAvailable,
                'before' => 0,
                'after' => $prices[0]->price,
                'variation' => 0,
                'average_variation' => 0,
            ]);

            return;
        }

        if (
            ! $prices[0]->available
            && $prices[1]->available
        ) {
            PriceNotification::create([
                'monitored_property_id' => $propertyId,
                'checkin' => $date,
                'type' => PriceNotificationTypeEnum::PriceUnavailable,
                'before' => $prices[1]->price,
                'after' => 0,
                'variation' => 0,
                'average_variation' => 0,
            ]);

            return;
        }

        if ($prices[0]->price > $prices[1]->price) {
            PriceNotification::create([
                'monitored_property_id' => $propertyId,
                'checkin' => $date,
                'type' => PriceNotificationTypeEnum::PriceUp,
                'before' => $prices[1]->price,
                'after' => $prices[0]->price,
                'variation' => number_format(
                    (($prices[0]->price - $prices[1]->price) / $prices[1]->price) * 100,
                    2
                ),
                'average_variation' => number_format((new PriceManager())->getVariationPercentageByModePrice(
                    $propertyId,
                    $prices[0]->price
                ), 2),
            ]);

            return;
        }

        if ($prices[0]->price < $prices[1]->price) {
            PriceNotification::create([
                'monitored_property_id' => $propertyId,
                'checkin' => $date,
                'type' => PriceNotificationTypeEnum::PriceDown,
                'before' => $prices[1]->price,
                'after' => $prices[0]->price,
                'variation' => number_format(
                    (($prices[0]->price - $prices[1]->price) / $prices[1]->price) * 100,
                    2
                ),
                'average_variation' => number_format((new PriceManager())->getVariationPercentageByModePrice(
                    $propertyId,
                    $prices[0]->price
                ), 2),
            ]);

            return;
        }
    }
}
