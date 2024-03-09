<?php

namespace App\Managers;

use App\Enums\PriceNotificationTypeEnum;
use App\Models\MonitoredData;
use App\Models\PriceNotification;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class CheckPriceManager
{
    public function checkPropertyPrices(int $propertyId): void
    {
        $lastPrice = MonitoredData::query()
            ->where('monitored_property_id', $propertyId)
            ->orderBy('checkin', 'desc')
            ->first();

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
            return ;
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
                'message' => "Price available: {$prices[0]->price}" . PHP_EOL
                    . "Date: {$prices[0]->checkin->format('l, d F Y')}"
                    . PHP_EOL . 'Price list:' . PHP_EOL
                    . $this->generatePriceList($prices),
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
                'message' => 'Price become unavailable' . PHP_EOL
                    . "Date: {$prices[0]->checkin->format('l, d F Y')}"
                    . PHP_EOL . 'Price list:' . PHP_EOL
                    . $this->generatePriceList($prices),
            ]);

            return;
        }

        if ($prices[0]->price > $prices[1]->price) {
            PriceNotification::create([
                'monitored_property_id' => $propertyId,
                'checkin' => $date,
                'type' => PriceNotificationTypeEnum::PriceUp,
                'message' => "Price up: {$prices[1]->price} -> {$prices[0]->price}" . PHP_EOL
                    . 'Increase: ' . number_format(
                        (($prices[0]->price - $prices[1]->price) / $prices[1]->price) * 100,
                        2
                    ) . '%' . PHP_EOL
                    . "Date: {$prices[0]->checkin->format('l, d F Y')}"
                    . PHP_EOL . 'Price list:' . PHP_EOL
                    . $this->generatePriceList($prices),
            ]);

            return;
        }

        if ($prices[0]->price < $prices[1]->price) {
            PriceNotification::create([
                'monitored_property_id' => $propertyId,
                'checkin' => $date,
                'type' => PriceNotificationTypeEnum::PriceDown,
                'message' => "Price down: {$prices[1]->price} -> {$prices[0]->price}" . PHP_EOL
                    . 'Decrease: ' . number_format(
                        (($prices[0]->price - $prices[1]->price) / $prices[1]->price) * 100,
                        2
                    ) . '%' . PHP_EOL
                    . "Date: {$prices[0]->checkin->format('l, d F Y')}"
                    . PHP_EOL . 'Price list:' . PHP_EOL
                    . $this->generatePriceList($prices),
            ]);

            return;
        }
    }

    public function generatePriceList(Collection $prices): string
    {
        $result = '';

        foreach ($prices as $price) {
            $result .= $price->created_at->format('Y-m-d') . ': ' . $price->price . PHP_EOL;
        }

        return $result;
    }
}
