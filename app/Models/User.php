<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'user_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'name',
        'username',
        'contact_number',
        'is_active',
        'email',
        'password',
        'role_id',
        'household_id',
        'must_change_password',
        'temp_password',
        'email_verified_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            $user->user_id ??= (string) Str::uuid();
        });
    }

    protected $hidden = [
        'password',
        'temp_password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'    => 'datetime',
        'password'             => 'hashed',
        'must_change_password' => 'boolean',
        'is_active'            => 'boolean',
        'role_id'              => 'integer',
    ];

    public function getIdAttribute(): ?string
    {
        return $this->attributes['user_id'] ?? null;
    }

    public function getUserIdAttribute(): ?string
    {
        return $this->attributes['user_id'] ?? null;
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function household()
    {
        return $this->belongsTo(Household::class, 'household_id', 'household_id');
    }

    public function normalizedRole(): ?string
    {
        return strtolower($this->role?->name ?? $this->attributes['role'] ?? '');
    }

    public function isAdmin(): bool
    {
        return $this->normalizedRole() === 'admin';
    }

    public function isCaptain(): bool
    {
        return in_array($this->normalizedRole(), ['captain', 'head'], true);
    }

    public function isEncoder(): bool
    {
        return $this->normalizedRole() === 'encoder';
    }

    public function isSuperAdmin(): bool
    {
        return in_array($this->normalizedRole(), ['super admin', 'admin'], true);
    }

    public function canDeleteHouseholds(): bool
    {
        return $this->isCaptain() || $this->isSuperAdmin();
    }

    public function canManageAccounts(): bool
    {
        return $this->hasPermission('manage_accounts') || $this->isSuperAdmin();
    }

    public function hasPermission(string $permission): bool
    {
        // Extend with a real permission system if needed
        $perms = [
            'manage_households' => ['admin', 'captain', 'head', 'encoder'],
            'view_households'   => ['admin', 'captain', 'head', 'encoder'],
            'manage_accounts'   => ['admin', 'super admin', 'captain', 'head', 'encoder'],
            'view_reports'      => ['admin', 'captain', 'head', 'encoder', 'super admin'],
            'register_accounts' => ['admin', 'super admin', 'captain', 'head', 'encoder'],
        ];

        if (!isset($perms[$permission])) {
            return false;
        }

        return in_array($this->normalizedRole(), $perms[$permission], true);
    }

    /**
     * Check if user can manage (create/update/delete) households.
     */
    public function canManageHouseholds(): bool
    {
        return $this->hasPermission('manage_households') || $this->isSuperAdmin();
    }

    /**
     * Check if user can view households.
     */
    public function canViewHouseholds(): bool
    {
        return $this->hasPermission('view_households') || $this->isSuperAdmin();
    }
}
