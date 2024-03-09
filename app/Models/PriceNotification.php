<?php

namespace App\Models;

use App\Enums\PriceNotificationTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitoredData extends Model
{
    use HasFactory;

    protected $table = 'price_notifications';

    protected $fillable = [
        'monitored_property_id',
        'type',
        'message',
    ];

    protected $casts = [
        'type' => PriceNotificationTypeEnum::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function monitoredProperty()
    {
        return $this->belongsTo(MonitoredProperty::class);
    }
}
