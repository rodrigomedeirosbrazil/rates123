<?php

namespace App\Models;

use App\Enums\RoomTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyRoom extends Model
{
    use HasFactory;

    protected $table = 'property_rooms';

    protected $fillable = [
        'property_id',
        'name',
        'type',
        'quantity',
        'percentage',
        'rate_room_id',
    ];

    protected $casts = [
        'type' => 'string',
        'quantity' => 'integer',
        'percentage' => 'integer',
        'type' => RoomTypeEnum::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'id');
    }
}
