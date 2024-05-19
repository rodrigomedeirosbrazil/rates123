<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertyProperty extends Model
{
    use HasFactory;

    protected $table = 'property_properties';

    public $timestamps = false;

    protected $fillable = [
        'property_id',
        'followed_property_id',
    ];

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'followed_property_id', 'id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'id');
    }
}
