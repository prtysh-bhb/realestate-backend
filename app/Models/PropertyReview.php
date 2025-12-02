<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyReview extends Model
{
    protected $table = 'property_reviews';

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
        'negative_comment',
        'is_visible'
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
