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
        'monitored_platform_id',
        'name',
        'url',
        'extra',
    ];

    protected $casts = [
        'extra' => 'array',
    ];

    public function platform()
    {
        return $this->belongsTo(MonitoredPlatform::class);
    }
}
