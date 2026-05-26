<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasUuids;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['name', 'role_name', 'role_key'];
    protected $appends = ['role_id', 'role_name'];

    public function getRoleIdAttribute(): ?string
    {
        return $this->attributes['id'] ?? null;
    }

    public function getRoleNameAttribute(): ?string
    {
        return $this->attributes['name'] ?? null;
    }

    public function setRoleNameAttribute(?string $value): void
    {
        $this->attributes['name'] = $value;
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }
}
