<?php

namespace App\Managers;

use App\Property\DTOs\PropertyDTO;
use App\Scraper\AirbnbScraper;
use App\Scraper\BookingScraper;
use App\Scraper\Contracts\ScraperContract;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ScrapManager
{
    public function getPrices(PropertyDTO $propertyDTO, CarbonInterface $from, int $days): Collection
    {
        $scraper = $this->loadScraper($propertyDTO->platformSlug);

        return $scraper->getPrices($propertyDTO->url, $from, $days);
    }

    public function loadScraper(string $platformSlug): ScraperContract
    {
        return match ($platformSlug) {
            'booking' => new BookingScraper(),
            'airbnb' => new AirbnbScraper(),
            default => throw new \Exception('Invalid platform slug'),
        };
    }
}
