<?php

namespace App\Models;

use App\Property\DTOs\PropertyDTO;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'scraped_platform_id',
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
        'hits_property_name',
    ];

    protected $casts = [
        'extra' => 'array',
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
    ];

    public function platform(): BelongsTo
    {
        return $this->belongsTo(ScrapedPlatform::class, 'scraped_platform_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_followed_properties', 'property_id', 'user_id');
    }

    public function followByUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function syncs(): HasMany
    {
        return $this->hasMany(Sync::class, 'property_id', 'id');
    }

    public function rates(): HasMany
    {
        return $this->hasMany(Rate::class, 'property_id', 'id');
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
