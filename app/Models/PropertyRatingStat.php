<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyRatingStat extends Model
{
    protected $table = 'property_rating_stats';

    protected $fillable = [
        'property_id',
        'avg_construction',
        'avg_amenities',
        'avg_management',
        'avg_connectivity',
        'avg_green_area',
        'avg_locality',
        'overall_rating'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
