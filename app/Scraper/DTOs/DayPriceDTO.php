<?php

namespace App\Scraper\DTOs;

use Carbon\CarbonInterface;

class DayPriceDTO
{
    public function __construct(
        public int $propertyId,
        public CarbonInterface $checkin,
        public float $price,
        public bool $available,
        public array $extra
    ) {
    }
}
