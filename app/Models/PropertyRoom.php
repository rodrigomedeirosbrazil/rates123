<?php

namespace App\Models;

use App\Enums\RoomTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Occupancy extends Model
{
    use HasFactory;

    protected $table = 'property_rooms';

    protected $fillable = [
        'property_id',
        'name',
        'type',
        'quantity',
    ];

    protected $casts = [
        'type' => 'string',
        'quantity' => 'integer',
        'type' => RoomTypeEnum::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'id');
    }
}
