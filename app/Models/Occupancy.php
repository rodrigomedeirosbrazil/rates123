<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;

class Occupancy extends Model
{
    use HasFactory;

    protected $table = 'occupancies';

    protected $fillable = [
        'property_id',
        'checkin',
        'total_rooms',
        'occupied_rooms',
    ];

    protected $casts = [
        'checkin' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'id');
    }

    protected function occupancyPercent(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => data_get($attributes, 'occupied_rooms') / data_get($attributes, 'total_rooms') * 100
        );
    }

    public function scopeAddMax($query, string $field)
    {
        return $query->select(['*', DB::raw("MAX($field) as max")]);
    }
}
