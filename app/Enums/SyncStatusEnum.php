<?php

namespace App\Enums;

enum SyncStatusEnum: string
{
    case Successful = 'successful';
    case Failed = 'failed';
    case InProgress = 'in_progress';
}
