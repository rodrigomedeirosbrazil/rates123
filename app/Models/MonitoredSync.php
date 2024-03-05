<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitoredSync extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'monitored_syncs';

    protected $fillable = [
        'monitored_property_id',
        'successful',
        'prices_count',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'successful' => 'boolean',
        'prices_count' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function monitoredProperty()
    {
        return $this->belongsTo(MonitoredProperty::class);
    }
}
