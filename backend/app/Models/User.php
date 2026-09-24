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
        'phone',
        'role',
        'account_status',
        'otp',
        'otp_expires_at',
        'email_verified_at',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'birthday' => 'date',
            'otp_expires_at' => 'datetime',
            'email_verified_at' => 'datetime',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->middle_initial . ' ' . $this->last_name);
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved' && $this->status === 'active';
    }

    public function cart()
    {
        return $this->hasOne(Cart::class, 'buyer_id');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'buyer_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'buyer_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    public function seller()
    {
        return $this->hasOne(Seller::class, 'user_id');
    }

    public function canSell(): bool
    {
        return $this->seller?->isApproved() ?? false;
    }
}