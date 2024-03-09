<?php

namespace App\Enums;

enum PriceNotificationTypeEnum: string
{
    case PriceUp = 'price-up';
    case PriceDown = 'price-down';
    case PriceAvailable = 'price-available';
    case PriceUnavailable = 'price-unavailable';
}
