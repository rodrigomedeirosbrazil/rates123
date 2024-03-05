<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitoredData extends Model
{
    use HasFactory;

    protected $table = 'monitored_datas';

    protected $fillable = [
        'monitored_property_id',
        'price',
        'checkin',
        'available',
        'extra',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'checkin' => 'date',
        'extra' => 'array',
    ];

    public function monitoredProperty()
    {
        return $this->belongsTo(MonitoredProperty::class);
    }
}
