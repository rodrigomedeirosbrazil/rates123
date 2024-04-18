<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Occupancy extends Model
{
    use HasFactory;

    protected $table = 'occupancies';

    protected $fillable = [
        'monitored_property_id',
        'checkin',
        'total_rooms',
        'occupied_rooms',
    ];

    protected $casts = [
        'checkin' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function monitoredProperty()
    {
        return $this->belongsTo(MonitoredProperty::class, 'monitored_property_id', 'id');
    }
}
