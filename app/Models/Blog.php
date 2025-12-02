<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'image', 'status'
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image
            ? asset('storage/blogs/' . $this->image)
            : null;
    }
}
