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
        'message',
    ];

    protected $casts = [
        'type' => PriceNotificationTypeEnum::class,
        'checkin' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function monitoredProperty()
    {
        return $this->belongsTo(MonitoredProperty::class, 'monitored_property_id', 'id');
    }
}
