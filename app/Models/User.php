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

    protected $fillable = [
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

    public function isSuperAdmin(): bool
    {
        return $this->role && in_array($this->role->name, ['Super Admin', 'Admin'], true);
    }

    public function hasPermission(string $permission): bool
    {
        // Extend with a real permission system if needed
        $perms = [
            'manage_households' => ['Admin', 'Captain', 'Encoder'],
            'view_households'     => ['Admin', 'Captain', 'Encoder'],
            'manage_accounts'     => ['Admin', 'Super Admin'],
            'view_reports'        => ['Admin', 'Captain', 'Super Admin'],
            'register_accounts'   => ['Admin', 'Super Admin'],
        ];

        if (!isset($perms[$permission])) {
            return false;
        }

        return in_array($this->role?->name, $perms[$permission], true);
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

