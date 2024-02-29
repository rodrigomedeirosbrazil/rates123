<?php

namespace App\Managers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ScrapManager
{
    public function getPrices(string $propertyUrl, int $pages): Collection
    {
        $prices = Http::get(
            config('app.scrap_url'),
            [
                'url' => $propertyUrl,
                'pages' => $pages,
            ]
        );

        return collect($prices);
    }
}
