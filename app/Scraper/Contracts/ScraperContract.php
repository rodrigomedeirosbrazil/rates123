<?php

namespace App\Scraper\Contracts;

use App\Scraper\DTOs\DayPriceDTO;
use App\Scraper\DTOs\PropertyDTO;

interface ScraperContract
{
    public string $endpoint;

    public function parsePrice(PropertyDTO $propertyDTO, array $price): DayPriceDTO;

    public function validatePrice(array $price): bool;
}
