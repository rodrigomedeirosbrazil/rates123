<?php

namespace App\Models;

use App\Property\DTOs\PropertyDTO;
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
        'country',
        'state',
        'city',
        'neighborhood',
        'address',
        'number',
        'complement',
        'postal_code',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'extra' => 'array',
        'latitude' => 'decimal:2',
        'longitude' => 'decimal:2',
    ];

    public function platform()
    {
        return $this->belongsTo(MonitoredPlatform::class, 'monitored_platform_id', 'id');
    }

    public function syncs()
    {
        return $this->hasMany(MonitoredSync::class, 'monitored_property_id', 'id');
    }

    public function priceDatas()
    {
        return $this->hasMany(MonitoredData::class, 'monitored_property_id', 'id');
    }

    public function toPropertyDTO(): PropertyDTO
    {
        return new PropertyDTO(
            id: $this->id,
            name: $this->name,
            url: $this->url,
            platformSlug: $this->platform->slug,
            extra: $this->extra,
        );
    }
}
