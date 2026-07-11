<?php

namespace App\Models;

use App\Models\Builders\RoleQueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Role extends Model
{
    protected $primaryKey = 'role_id';
    public    $keyType    = 'int';
    public    $incrementing = false;
    public    $timestamps = false;

    protected $fillable = ['name', 'role_id', 'role_key', 'role_name'];

    /** Map of normalized role keys to their canonical display names */
    private const DISPLAY_NAME_MAP = [
        'super_admin' => 'Captain',
        'admin'       => 'Captain',
        'captain'     => 'Captain',
        'evac_admin'  => 'Moderator',
        'moderator'   => 'Moderator',
        'evac_personnel'  => 'personel',
        'personnel'   => 'personel',
        'personel'    => 'personel',
        'rescuer'     => 'personel',
        'encoder'     => 'Encoder',
        'household_resident' => 'Household',
        'household'   => 'Household',
    ];

    // ── Lifecycle hooks ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Role $role) {
            if (empty($role->role_id)) {
                $role->role_id = (static::max('role_id') ?? 0) + 1;
            }
            if (empty($role->role_key)) {
                $rawName      = $role->attributes['role_name'] ?? $role->attributes['name'] ?? 'role';
                $role->role_key = Str::slug($rawName, '_');
            }
        });

        static::saving(function (Role $role) {
            $table = $role->getTable();
            foreach (['name', 'role_name', 'role_key'] as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    unset($role->attributes[$column]);
                }
            }
        });
    }

    // ── Accessors / Mutators ──────────────────────────────────────────────────

    public function getNameAttribute(): ?string
    {
        $key  = $this->attributes['role_key'] ?? null;
        $name = $this->attributes['role_name'] ?? $this->attributes['name'] ?? null;

        $lookup = $key ?? $name;
        if ($lookup) {
            $normalized = strtolower(trim($lookup));
            return self::DISPLAY_NAME_MAP[$normalized] ?? null;
        }

        return $name;
    }

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['role_name'] = $value;
        $this->attributes['name']      = $value;
    }

    public function getRoleNameAttribute(): ?string
    {
        return $this->getNameAttribute();
    }

    public function setRoleNameAttribute(?string $value): void
    {
        $this->attributes['role_name'] = $value;
        $this->attributes['name']      = $value;
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }

    // ── Custom query builder ──────────────────────────────────────────────────

    public function newEloquentBuilder($query): RoleQueryBuilder
    {
        return new RoleQueryBuilder($query);
    }
}
