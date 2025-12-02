<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'location', 
        'avatar',
        'bio',
        'company_name',
        'license_number',
        'address',
        'city',
        'state',
        'zipcode',
        'is_active',
        'deactivation_reason',
        'deactivated_at',
        'provider',
        'provider_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'deactivated_at' => 'datetime',
    ];

    protected $appends = ['avatar_url'];

    // Accessor for avatar URL
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return null;
    }

    // Check if user is admin
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    // Check if user is agent
    public function isAgent()
    {
        return $this->role === 'agent';
    }

    // Check if user is customer
    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    // Relationships
    public function properties()
    {
        return $this->hasMany(Property::class, 'agent_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function inquiries()
    {
        return $this->hasMany(Inquiry::class, 'customer_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(UserSubscription::class)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->latest();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function agentAppointments()
    {
        return $this->hasMany(Appointment::class, 'agent_id');
    }

    public function customerAppointments()
    {
        return $this->hasMany(Appointment::class, 'customer_id');
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class, 'agent_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(UserSubscription::class)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->latest();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function agentAppointments()
    {
        return $this->hasMany(Appointment::class, 'agent_id');
    }

    public function customerAppointments()
    {
        return $this->hasMany(Appointment::class, 'customer_id');
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class, 'agent_id');
    }
}