<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    protected $fillable = [
        'name',
        'price',
        'coins',
        'description',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'coins' => 'integer',
    ];
}