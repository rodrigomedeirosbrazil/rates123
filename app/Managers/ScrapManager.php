<?php

namespace App\Managers;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ScrapManager
{
    public function getBookingPrices(string $propertyUrl, int $pages): Collection
    {
        $response = Http::timeout(110)
            ->get(
                config('app.scrap.url') . '/booking/prices',
                [
                    'url' => $propertyUrl,
                    'pages' => $pages,
                ]
            );

        $prices = $response->json();

        return collect($prices);
    }

    public function getAirbnbPrices(string $propertyUrl, CarbonInterface $fromDate, int $days): Collection
    {
        $response = Http::timeout(110)
            ->get(
                config('app.scrap.url') . '/airbnb/prices',
                [
                    'url' => $propertyUrl,
                    'fromDate' => $fromDate->toDateString(),
                    'days' => $days,
                ]
            );

        $prices = $response->json();

        return collect($prices);
    }
}
