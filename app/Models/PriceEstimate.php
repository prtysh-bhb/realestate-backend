<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceEstimate extends Model
{
    protected $fillable = [
        'agent_id',
        'property_id',
        'property_details',
        'estimated_price',
        'price_range_min',
        'price_range_max',
        'ai_reasoning',
        'comparables',
        'breakdown',
        'suggested_listing_price',
    ];

    protected $casts = [
        'property_details' => 'array',
        'estimated_price' => 'decimal:2',
        'price_range_min' => 'decimal:2',
        'price_range_max' => 'decimal:2',
        'suggested_listing_price' => 'decimal:2',
        'comparables' => 'array',
        'breakdown' => 'array',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}