<?php

namespace App\Enums;

use App\Enums\Traits\Valuable;

enum ScheduleEventTypeEnum: string
{
    use Valuable;

    case Holiday = 'holiday';
    case Concert = 'concert';
    case Championship = 'championship';
    case Conference = 'conference';
}
