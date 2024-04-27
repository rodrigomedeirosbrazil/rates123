<?php

namespace App\DTOs;

use Carbon\CarbonInterface;

class OccupancyDiffDTO
{
    public function __construct(
        public CarbonInterface $checkin,
        public int $oldOccupancy,
        public int $newOccupancy,
    ) {
    }
}
