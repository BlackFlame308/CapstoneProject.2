<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
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
        'role_id',
        'household_id',
        'must_change_password',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'must_change_password' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === 'Admin';
    }

    public function isCaptain(): bool
    {
        return $this->role && $this->role->name === 'Captain';
    }

    public function isEncoder(): bool
    {
        return $this->role && $this->role->name === 'Encoder';
    }
}
