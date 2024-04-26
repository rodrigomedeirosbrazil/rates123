<?php

namespace App\Models;

use App\Enums\SyncStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sync extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'syncs';

    protected $fillable = [
        'property_id',
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

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public static function propertyIsSyncedToday(int $propertyId): bool
    {
        return self::query()
            ->wherePropertyId($propertyId)
            ->whereDate('started_at', now())
            ->where('status', SyncStatusEnum::Successful)
            ->exists();
    }
}
