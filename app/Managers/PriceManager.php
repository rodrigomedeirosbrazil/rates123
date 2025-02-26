<?php

namespace App\Managers;

use App\Enums\PriceNotificationTypeEnum;
use App\Models\Rate;
use App\Models\Property;
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

        $property = Property::findOrFail($propertyId);

        $mode = $property->rates
            ->filter(fn ($price) => $price->available === true)
            ->where(fn ($price) => $price->checkin > now()->subYear())
            ->mode('price')[0];

        cache()->put($cacheKey, (float) $mode, now()->addDay());

        return (float) $mode;
    }

    public function calculateCheckinPropertyModePrice(int $propertyId, CarbonInterface $checkin): float
    {
        $mode = Rate::query()
            ->where('property_id', $propertyId)
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

        return $userModel->userProperties
            ->mapWithkeys(
                function ($userProperty) use ($createdAt) {
                    $property = $userProperty->property;

                    $followedPropertyIds = $property->followProperties
                        ->pluck('followed_property_id');

                    $searchDate = $createdAt ?? now();

                    $priceNotifications = PriceNotification::query()
                        ->whereDate('created_at', $searchDate)
                        ->whereIn('property_id', $followedPropertyIds)
                        ->orderBy('checkin', 'asc')
                        ->get();

                    return ["{$property->name}" => $priceNotifications];
                }
            );
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

        return $priceNotifications
            ->map(
                function (Collection $priceNotificationOfProperty, $key) {
                    $priceNotificationTextOfProperty = $priceNotificationOfProperty->map(
                        function (PriceNotification $priceNotification) {
                            $basicInfo = [
                                __('Checkin') . ': ' . $priceNotification->checkin->translatedFormat('l, d F y') . PHP_EOL,
                                __('Type') . ': ' . __($priceNotification->type->value) . PHP_EOL,
                                __('Property') . ': ' . $priceNotification->property->name . PHP_EOL,
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
                    );

                    return array_merge(
                        [__('Price Notifications to') . ' ' . $key . PHP_EOL],
                        [PHP_EOL],
                        $priceNotificationTextOfProperty->flatten()->toArray(),
                    );
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

        $followedPropertyIds = $userModel->followProperties->pluck('id');

        if ($followedPropertyIds->isEmpty()) {
            return collect();
        }

        $prices = PriceNotification::query()
            ->whereIn('property_id', $followedPropertyIds)
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
