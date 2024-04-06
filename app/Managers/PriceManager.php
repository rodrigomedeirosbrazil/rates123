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

    public function buildPriceNotificationsList(User $user, CarbonInterface $date = null): Collection
    {
        $priceNotifications = $this->getUserPriceNotificationsByCreatedAt($user, $date ?? today());

        if ($priceNotifications->isEmpty()) {
            return collect();
        }

        return $priceNotifications->map(
            fn (PriceNotification $priceNotification) => (object) [
                ...$priceNotification->toArray(),
                'variation_mean_price' => $this->getVariationPercentageByModePrice(
                    $priceNotification->monitored_property_id,
                    $priceNotification->after
                ),
            ]
        );
    }

    public function buildPriceNotificationsTextList(User $user, CarbonInterface $date = null): ?string
    {
        $priceNotifications = $this->buildPriceNotificationsList($user, $date ?? today());

        return $priceNotifications->map(
            fn (PriceNotification $priceNotification) => [
                __('Checkin') . ': ' . $priceNotification->checkin->translatedFormat('l, d F y') . PHP_EOL,
                __('Property') . ': ' . $priceNotification->monitoredProperty->name . PHP_EOL,
                __('Type') . ': ' . __($priceNotification->type->value) . PHP_EOL,
                __('Before') . ": \${$priceNotification->before}" . PHP_EOL,
                __('After') . ": \${$priceNotification->after}" . PHP_EOL,
                __('Change') . ": {$priceNotification->variation}%" . PHP_EOL,
                __('Variation Mean Price') . ': '
                    . number_format($this->getVariationPercentageByModePrice(
                        $priceNotification->monitored_property_id,
                        $priceNotification->after
                    ), 2)
                    . '%' . PHP_EOL,
                PHP_EOL,
            ]
        )->flatten()->implode('');
    }
}
