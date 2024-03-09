<?php

namespace App\Scraper;

use App\Scraper\Contracts\ScraperContract;
use App\Scraper\DTOs\PropertyDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

abstract class Scraper implements ScraperContract
{
    public int $timeout = 110;

    public function getPrices(PropertyDTO $propertyDTO, array $params): Collection
    {
        $response = Http::timeout($this->timeout)
            ->get(
                config('app.scrap.url') . $this->endpoint,
                [
                    ...$params,
                    'url' => $propertyDTO->url,
                ]
            );

        $prices = $response->json();

        return collect($prices)
            ->filter(fn ($price) => $this->validatePrice($price))
            ->map(fn ($price) => $this->parsePrice($propertyDTO, $price));
    }
}
