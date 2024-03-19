<?php

namespace App\Models;

use App\Enums\DateEventTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DateEvent extends Model
{
    use HasFactory;

    protected $table = 'date_events';

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
        'type' => DateEventTypeEnum::class,
        'begin' => 'date',
        'end' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
