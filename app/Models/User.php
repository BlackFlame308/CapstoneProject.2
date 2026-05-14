<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasUuids;

    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'username',
        'contact_number',
        'is_active',
        'email',
        'password',
        'role_id',
        'role',
        'household_id',
        'must_change_password',
        'temp_password',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'temp_password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'must_change_password' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getIdAttribute(): ?string
    {
        return $this->attributes['id'] ?? null;
    }

    public function getUserIdAttribute(): ?string
    {
        return $this->attributes['id'] ?? null;
    }

    public function setUserIdAttribute(?string $value): void
    {
        $this->attributes['id'] = $value;
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    protected function normalizedRole(): ?string
    {
        return strtolower($this->role?->name ?? '');
    }

    public function isAdmin(): bool
    {
        return $this->normalizedRole() === 'admin';
    }

    public function isCaptain(): bool
    {
        return $this->normalizedRole() === 'captain';
    }

    public function isEncoder(): bool
    {
        return $this->normalizedRole() === 'encoder';
    }

    public function isSuperAdmin(): bool
    {
        return in_array($this->normalizedRole(), ['super admin', 'admin'], true);
    }

    public function hasPermission(string $permission): bool
    {
        // Extend with a real permission system if needed
        $perms = [
            'manage_households' => ['admin', 'captain', 'encoder'],
            'view_households'   => ['admin', 'captain', 'encoder'],
            'manage_accounts'  => ['admin', 'super admin'],
            'view_reports'     => ['admin', 'captain', 'super admin'],
            'register_accounts'=> ['admin', 'super admin'],
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
