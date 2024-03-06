<?php

namespace App\Managers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ScrapManager
{
    public function getPrices(string $propertyUrl, int $pages): Collection
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
}
