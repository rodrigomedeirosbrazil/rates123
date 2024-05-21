<?php

namespace App\Managers;

use App\DTOs\PriceNotificationDTO;
use App\Enums\PriceNotificationTypeEnum;
use App\Models\Rate;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;

class CheckPriceManager
{
    public function checkPropertyPrices(int $propertyId): Collection
    {
        $lastPrice = Rate::query()
            ->where('property_id', $propertyId)
            ->orderBy('checkin', 'desc')
            ->firstOrFail();

        $date = now()->addDay();

        $priceNotificationDTOs = collect([]);

        while ($lastPrice->checkin->gte($date)) {
            $priceNotificationDTO = $this->checkPriceDate($propertyId, $date);

            if ($priceNotificationDTO) {
                $priceNotificationDTOs->push($priceNotificationDTO);
            }

            $date->addDay();
        }

        return $priceNotificationDTOs;
    }

    public function checkPriceDate(int $propertyId, CarbonInterface $date): ?PriceNotificationDTO
    {
        $prices = Rate::query()
            ->where('property_id', $propertyId)
            ->whereDate('checkin', $date)
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();

        if ($prices->count() < 2) {
            return null;
        }

        $newPrice = $prices[0];
        $oldPrice = $prices[1];

        if (
            ($newPrice->available
                && $oldPrice->available
                && $newPrice->price === $oldPrice->price)
            || (! $newPrice->available && ! $oldPrice->available)
        ) {
            return null;
        }

        if (
            $newPrice->available
            && ! $oldPrice->available
        ) {
            return new PriceNotificationDTO(
                propertyId: $propertyId,
                checkin: $date,
                type: PriceNotificationTypeEnum::PriceAvailable,
                oldPrice: 0,
                newPrice: $newPrice->price,
                variationToLastPrice: 0,
                variationToBasePrice: 0,
            );
        }

        if (
            ! $newPrice->available
            && $oldPrice->available
        ) {
            return new PriceNotificationDTO(
                propertyId: $propertyId,
                checkin: $date,
                type: PriceNotificationTypeEnum::PriceUnavailable,
                oldPrice: $oldPrice->price,
                newPrice: 0,
                variationToLastPrice: 0,
                variationToBasePrice: 0,
            );
        }

        if ($newPrice->price > $oldPrice->price) {
            $propertyBasePrice = (new PriceManager())->calculatePropertyModePrice($propertyId);

            return new PriceNotificationDTO(
                propertyId: $propertyId,
                checkin: $date,
                type: PriceNotificationTypeEnum::PriceUp,
                oldPrice: $oldPrice->price,
                newPrice: $newPrice->price,
                variationToLastPrice: Number::format(($newPrice->price - $oldPrice->price) / $oldPrice->price * 100, 0),
                variationToBasePrice: Number::format(($newPrice->price - $propertyBasePrice) / $propertyBasePrice * 100, 0),
            );
        }

        if ($newPrice->price < $oldPrice->price) {
            $propertyBasePrice = (new PriceManager())->calculatePropertyModePrice($propertyId);

            return new PriceNotificationDTO(
                propertyId: $propertyId,
                checkin: $date,
                type: PriceNotificationTypeEnum::PriceDown,
                oldPrice: $oldPrice->price,
                newPrice: $newPrice->price,
                variationToLastPrice: Number::format(($newPrice->price - $oldPrice->price) / $oldPrice->price * 100, 0),
                variationToBasePrice: Number::format(($newPrice->price - $propertyBasePrice) / $propertyBasePrice * 100, 0),
            );
        }

        return null;
    }

    public function processPrices(int $propertyId, Collection $prices): void
    {
        $prices->each(function ($price) use ($propertyId) {
            $rates = Rate::query()
                ->where('property_id', $propertyId)
                ->whereDate('checkin', $price->checkin)
                ->orderBy('created_at', 'desc')
                ->limit(2)
                ->get();

            if (
                $rates->count() > 0
                && $rates->first()->price == $price->price
            ) {
                $rates->first()->min_stay = $price->minStay;
                $rates->first()->extra = $price->extra ?? '[]';
                $rates->first()->updated_at = now();
                $rates->first()->save();

                return;
            }

            if (
                $rates->count() === 2
                && ! $rates->first()->available
                && $rates->first()->created_at->isToday()
                && $rates->last()->available
                && ! $rates->last()->created_at->isToday()
            ) {
                $rates->first()->forceDelete();

                if ($rates->last()->price == $price->price) {
                    $rates->last()->min_stay = $price->minStay;
                    $rates->last()->extra = $price->extra ?? '[]';
                    $rates->last()->updated_at = now();
                    $rates->last()->save();

                    return;
                }
            }

            Rate::create([
                'property_id' => $propertyId,
                'price' => $price->price,
                'checkin' => $price->checkin,
                'available' => $price->available,
                'min_stay' => $price->minStay,
                'extra' => $price->extra ?? '[]',
            ]);
        });
    }

    public function getGroupUnavailableConsecutiveDates(int $propertyId): Collection
    {
        $rates = Rate::query()
            ->unavailableAndUpdatedToday($propertyId)
            ->get();

        $grouped = collect([]);
        $group = collect([]);

        $rates->each(function ($rate) use (&$grouped, &$group) {
            if ($group->isNotEmpty() && $group->last()->checkin->diffInDays($rate->checkin) > 1) {
                $grouped[] = $group;
                $group = collect([]);
            }

            $group[] = $rate;
        });

        if ($group) {
            $grouped[] = $group;
        }

        return $grouped;
    }
}
