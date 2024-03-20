<?php

namespace App\Models;

use App\Enums\SyncStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitoredSync extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'monitored_syncs';

    protected $fillable = [
        'monitored_property_id',
        'status',
        'prices_count',
        'started_at',
        'finished_at',
        'exception',
    ];

    protected $casts = [
        'status' => SyncStatusEnum::class,
        'prices_count' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function monitoredProperty()
    {
        return $this->belongsTo(MonitoredProperty::class);
    }

    public static function propertyIsSyncedToday(int $monitoredPropertyId): bool
    {
        return self::query()
            ->whereMonitoredPropertyId($monitoredPropertyId)
            ->whereDate('started_at', now())
            ->where('status', SyncStatusEnum::Successful)
            ->exists();
    }
}
