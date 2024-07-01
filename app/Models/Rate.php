<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Rate extends Model
{
    use HasFactory;

    protected $table = 'rates';

    protected $fillable = [
        'property_id',
        'price',
        'checkin',
        'available',
        'min_stay',
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

    public function scopeUnavailableAndUpdatedToday($query, int $propertyId)
    {
        return $query
            ->where('property_id', $propertyId)
            ->where('available', false)
            ->whereDate('checkin', '>', today())
            ->whereDate('updated_at', today())
            ->orderBy('checkin');
    }

    public function scopeAddMax($query, string $field)
    {
        return $query->select(['*', DB::raw("MAX($field) as max")]);
    }
}
