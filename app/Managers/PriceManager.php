<?php

namespace App\Managers;

use App\Enums\PriceNotificationTypeEnum;
use App\Models\MonitoredData;
use App\Models\MonitoredProperty;
use App\Models\PriceNotification;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class PriceManager
{
    public function calculatePropertyModePrice(int $propertyId, bool $noCache = false): float
    {
        $cacheKey = "propertyModePrice-{$propertyId}";

        if (cache()->has($cacheKey) && ! $noCache) {
            return (float) cache()->get($cacheKey);
        }

        $property = MonitoredProperty::findOrFail($propertyId);

        $mode = $property->priceDatas
            ->filter(fn ($price) => $price->available === true)
            ->where(fn ($price) => $price->checkin > now()->subYear())
            ->mode('price')[0];

        cache()->put($cacheKey, (float) $mode, now()->addDay());

        return (float) $mode;
    }

    public function calculateCheckinPropertyModePrice(int $propertyId, CarbonInterface $checkin): float
    {
        $mode = MonitoredData::query()
            ->where('monitored_property_id', $propertyId)
            ->where('checkin', $checkin)
            ->where('available', true)
            ->get()
            ->mode('price')[0];

        return (float) $mode;
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

    public function getVariationPercentageByModePrice(int $propertyId, float $price, bool $noCache = false): float
    {
        $propertyModePrice = $this->calculatePropertyModePrice($propertyId, $noCache);

        return ($price - $propertyModePrice) / $propertyModePrice * 100;
    }

    public function buildPriceNotificationsTextList(Collection $priceNotifications): ?string
    {
        if ($priceNotifications->isEmpty()) {
            return null;
        }

        return $priceNotifications->map(
            function (PriceNotification $priceNotification) {
                $basicInfo = [
                    __('Checkin') . ': ' . $priceNotification->checkin->translatedFormat('l, d F y') . PHP_EOL,
                    __('Type') . ': ' . __($priceNotification->type->value) . PHP_EOL,
                    __('Property') . ': ' . $priceNotification->monitoredProperty->name . PHP_EOL,
                    __('Before') . ": \${$priceNotification->before}" . PHP_EOL,
                    __('After') . ": \${$priceNotification->after}" . PHP_EOL,
                ];

                $variations = $priceNotification->type === PriceNotificationTypeEnum::PriceUp
                    || $priceNotification->type === PriceNotificationTypeEnum::PriceDown
                    ? [
                        __('Variation') . ': ' . number_format($priceNotification->variation, 2) . '%' . PHP_EOL,
                        __('Avg Variation') . ': ' . number_format($priceNotification->averageVariation, 2) . '%' . PHP_EOL,
                    ]
                    : [];

                return array_merge($basicInfo, $variations, [PHP_EOL]);
            }
        )
            ->flatten()->implode('');
    }

    public function buildPriceSuggestionsTextList(
        Collection $priceNotifications,
        User $user
    ): ?string {
        if ($priceNotifications->isEmpty()) {
            return null;
        }

        return $priceNotifications
            ->groupBy('checkin')
            ->map(fn ($checkinGroup) => $checkinGroup->first())
            ->map(
                function (PriceNotification $priceNotification) use ($user) {
                    $priceSuggestion = $this->createPriceSuggestionForDate(
                        $user,
                        $priceNotification->checkin
                    );

                    return $priceNotification->checkin->translatedFormat('l, d F y')
                        . ': '
                        . number_format($priceSuggestion, 0) . '%'
                        . PHP_EOL;
                }
            )
            ->flatten()->implode('');
    }

    public function createPriceSuggestionForDate(int | User $user, CarbonInterface $checkin): float
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

        $prices = PriceNotification::query()
            ->whereIn('monitored_property_id', $followedPropertyIds)
            ->whereDate('checkin', $checkin)
            ->whereIn('type', [PriceNotificationTypeEnum::PriceUp, PriceNotificationTypeEnum::PriceDown])
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('averageVariation');

        if ($prices->isEmpty()) {
            return 0;
        }

        return $prices->median();
    }
}
