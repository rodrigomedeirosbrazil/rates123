<?php

namespace App\Models;

use App\Enums\PriceNotificationTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceNotification extends Model
{
    use HasFactory;

    protected $table = 'price_notifications';

    protected $fillable = [
        'monitored_property_id',
        'checkin',
        'type',
        'variation',
        'average_variation',
        'before',
        'after',
    ];

    protected $casts = [
        'type' => PriceNotificationTypeEnum::class,
        'checkin' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'variation' => 'decimal:2',
        'average_variation' => 'decimal:2',
        'before' => 'decimal:2',
        'after' => 'decimal:2',
    ];

    public function monitoredProperty()
    {
        return $this->belongsTo(MonitoredProperty::class, 'monitored_property_id', 'id');
    }

    public function monitoredDatas()
    {
        return $this->hasMany(MonitoredData::class, 'monitored_property_id', 'monitored_property_id');
    }
}
