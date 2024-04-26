<?php

namespace App\Models;

use App\Enums\ScheduleEventTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleEvent extends Model
{
    use HasFactory;

    protected $table = 'schedule_events';

    protected $fillable = [
        'name',
        'begin',
        'end',
        'type',
        'country',
        'state',
        'city',
    ];

    protected $casts = [
        'type' => ScheduleEventTypeEnum::class,
        'begin' => 'date',
        'end' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
