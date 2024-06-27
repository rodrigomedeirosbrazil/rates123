<?php

namespace App\Enums;

use App\Enums\Traits\Valuable;

enum RoomTypeEnum: string
{
    use Valuable;

    case Single = 'single';
    case Twin = 'twin';
    case Double = 'double';
    case Triple = 'triple';
    case Quadruple = 'quadruple';
    case Connecting = 'connecting';
}
