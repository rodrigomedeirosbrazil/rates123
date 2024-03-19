<?php

namespace App\Enums;

enum DateEventTypeEnum: string
{
    case Holiday = 'holiday';
    case Concert = 'concert';
    case Championship = 'championship';
    case Conference = 'conference';
}
