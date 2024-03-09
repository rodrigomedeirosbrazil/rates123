<?php

namespace App\Scraper\Contracts;

use App\Scraper\DTOs\DayPriceDTO;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

interface ScraperContract
{
    public string $endpoint;

    public function getPrices(string $url, CarbonInterface $from, int $days): Collection;

    public function parsePrice(array $responsePrice): DayPriceDTO;

    public function validatePrice(array $responsePrice): bool;
}
