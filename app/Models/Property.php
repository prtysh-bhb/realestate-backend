<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = [
        'agent_id',
        'title',
        'description',
        'price',
        'location',
        'address',
        'city',
        'state',
        'zipcode',
        'type',
        'property_type',
        'bedrooms',
        'bathrooms',
        'area',
        'amenities',
        'images',
        'primary_image',
        'video',
        'documents',
        'status',
        'approval_status',
        'rejection_reason',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'area' => 'decimal:2',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'approved_at' => 'datetime',
        'amenities' => 'array',
        'images' => 'array',
        'documents' => 'array',
    ];

    protected $appends = ['primary_image_url', 'image_urls', 'document_urls', 'property_type_label', 'video_url'];

    public function getPrimaryImageUrlAttribute()
    {
        if ($this->primary_image) {
            return asset('storage/' . $this->primary_image);
        }
        return null;
    }

    public function getImageUrlsAttribute()
    {
        if ($this->images && is_array($this->images)) {
            return array_map(function($path) {
                return asset('storage/' . $path);
            }, $this->images);
        }
        return [];
    }

    public function getDocumentUrlsAttribute()
    {
        if ($this->documents && is_array($this->documents)) {
            return array_map(function($doc) {
                return [
                    'name' => $doc['name'],
                    'url' => asset('storage/' . $doc['path']),
                    'size' => $doc['size'] ?? null,
                ];
            }, $this->documents);
        }
        return [];
    }

    // Relationships
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    public function isFavoritedBy($userId)
    {
        return $this->favorites()->where('user_id', $userId)->exists();
    }

    public function getPropertyTypeLabelAttribute()
    {
        $propertyTypes = config('amenities.property_types', []);
        return $propertyTypes[$this->property_type] ?? $this->property_type;
    }

    public function getVideoUrlAttribute()
    {
        if ($this->video) {
            return asset('storage/' . $this->video);
        }
        return null;
    }

    public function views()
    {
        return $this->hasMany(PropertyView::class);
    }

    public function viewsCount()
    {
        return $this->views()->count();
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}