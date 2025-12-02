<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'property_id',
        'user_id',
        'construction',
        'amenities',
        'management',
        'connectivity',
        'green_area',
        'locality',
        'positive_comment',
        'negative_comment'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAverageAttribute()
    {
        return round((
            $this->construction +
            $this->amenities +
            $this->management +
            $this->connectivity +
            $this->green_area +
            $this->locality
        ) / 6, 1);
    }
}
