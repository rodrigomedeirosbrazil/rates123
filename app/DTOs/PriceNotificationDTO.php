<?php

namespace App\DTOs;

use App\Enums\PriceNotificationTypeEnum;
use Carbon\CarbonInterface;

class PriceNotificationDTO
{
    public function __construct(
        public int $propertyId,
        public string $propertyName,
        public CarbonInterface $checkin,
        public PriceNotificationTypeEnum $type,
        public float $oldPrice,
        public float $newPrice,
        public int $variationToLastPrice,
        public int $variationToBasePrice,
    ) {
    }
}
