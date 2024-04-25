<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    use HasFactory;

    protected $table = 'rates';

    protected $fillable = [
        'property_id',
        'price',
        'checkin',
        'available',
        'extra',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'checkin' => 'date',
        'available' => 'boolean',
        'extra' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
