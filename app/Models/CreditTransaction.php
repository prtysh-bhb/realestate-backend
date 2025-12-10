<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditTransaction extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'property_id',
        'type',
        'credits',
        'description',
        'meta_data',
    ];

    protected $casts = [
        'credits' => 'integer',
        'meta_data' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}