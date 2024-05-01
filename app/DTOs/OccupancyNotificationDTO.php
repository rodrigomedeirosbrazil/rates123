<?php

namespace App\DTOs;

use Carbon\CarbonInterface;

class OccupancyNotificationDTO
{
    public function __construct(
        public CarbonInterface $checkin,
        public OccupancyDiffDTO $occupancyDiffDTO,
    ) {
    }
}
