<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rate extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'begin',
        'end',
        'price',
    ];

    protected $casts = [
        'begin' => 'date',
        'end' => 'date',
        'price' => 'decimal:2',
    ];

    protected $dates = [
        'begin',
        'end',
    ];
}
