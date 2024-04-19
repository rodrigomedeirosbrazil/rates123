<?php

namespace App\Scraper\DTOs;

use Carbon\CarbonInterface;

class OccupancyDTO
{
    public function __construct(
        public CarbonInterface $checkin,
        public int $totalRooms,
        public int $occupiedRooms,
    ) {
    }
}
