<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'description',
        'featured_image',
        'image',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'published_at',
        'views_count',
        'meta_tags',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'published_at' => 'datetime',
        'meta_tags' => 'array',
        'views_count' => 'integer',
    ];

    protected $appends = ['image_url'];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class);
    }

    public function approvedComments(): HasMany
    {
        return $this->hasMany(BlogComment::class)->where('is_approved', true);
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        $imagePath = $this->featured_image ?? $this->image;
        
        if (!$imagePath) {
            return null;
        }

        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return $imagePath;
        }

        return asset('storage/' . $imagePath);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'approved')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}