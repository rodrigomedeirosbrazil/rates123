<?php

namespace App\Enums;

use App\Enums\Traits\Valuable;

enum SyncStatusEnum: string
{
    use Valuable;

    case Successful = 'successful';
    case Failed = 'failed';
    case InProgress = 'in_progress';
}
