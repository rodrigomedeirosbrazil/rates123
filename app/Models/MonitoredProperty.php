<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MonitoredProperty extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'url',
        'capture_months_number',
        'extra',
    ];

    protected $casts = [
        'extra' => 'array',
        'capture_months_number' => 'integer',
    ];
}
