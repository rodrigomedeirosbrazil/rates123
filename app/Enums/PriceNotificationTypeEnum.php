<?php

namespace App\Enums;

use App\Enums\Traits\Valuable;

enum PriceNotificationTypeEnum: string
{
    use Valuable;

    case PriceUp = 'price-up';
    case PriceDown = 'price-down';
    case PriceAvailable = 'price-available';
    case PriceUnavailable = 'price-unavailable';
}
