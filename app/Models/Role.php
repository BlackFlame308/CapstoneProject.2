<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $primaryKey = 'role_id';
    public $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = ['name'];
    protected $appends = ['role_name'];

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
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }
}
