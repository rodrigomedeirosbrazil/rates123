<?php

namespace App\Models;

use App\Scraper\DTOs\PropertyDTO;
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
    ];

    protected $casts = [
        'extra' => 'array',
    ];

    public function platform()
    {
        return $this->belongsTo(MonitoredPlatform::class, 'monitored_platform_id', 'id');
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
