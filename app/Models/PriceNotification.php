<?php

namespace App\Models;

use App\Enums\PriceNotificationTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class PriceNotification extends Model
{
    use HasFactory;

    protected $table = 'price_notifications';

    protected $fillable = [
        'monitored_property_id',
        'checkin',
        'type',
        'average_price',
        'before',
        'after',
    ];

    protected $casts = [
        'checkin' => 'date',
        'type' => PriceNotificationTypeEnum::class,
        'average_price' => 'decimal:2',
        'before' => 'decimal:2',
        'after' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function monitoredProperty()
    {
        return $this->belongsTo(MonitoredProperty::class, 'monitored_property_id', 'id');
    }

    public function monitoredDatas()
    {
        return $this->hasMany(MonitoredData::class, 'monitored_property_id', 'monitored_property_id');
    }

    protected function variation(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => data_get($attributes, 'type') === PriceNotificationTypeEnum::PriceUp->value
                || data_get($attributes, 'type') === PriceNotificationTypeEnum::PriceDown->value
                ? (data_get($attributes, 'after') - data_get($attributes, 'before')) / data_get($attributes, 'before') * 100
                : 0
        );
    }

    protected function averageVariation(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => data_get($attributes, 'type') === PriceNotificationTypeEnum::PriceUp->value
                || data_get($attributes, 'type') === PriceNotificationTypeEnum::PriceDown->value
                ? (data_get($attributes, 'after') - data_get($attributes, 'average_price')) / data_get($attributes, 'average_price') * 100
                : 0
        );
    }
}
