<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $primaryKey = 'role_id';
    public $keyType = 'int';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['name', 'role_id', 'role_key', 'role_name'];

    protected static function booted()
    {
        static::creating(function (Role $role) {
            if (empty($role->role_id)) {
                $role->role_id = (static::max('role_id') ?? 0) + 1;
            }
            if (empty($role->role_key)) {
                $role->role_key = \Illuminate\Support\Str::slug($role->role_name ?? $role->name ?? 'role', '_');
            }
        });
    }

    public function getNameAttribute(): ?string
    {
        $key = $this->attributes['role_key'] ?? null;
        $name = $this->attributes['role_name'] ?? $this->attributes['name'] ?? null;
        
        $check = $key ?? $name;
        if ($check) {
            $normalized = strtolower(trim($check));
            if (in_array($normalized, ['super_admin', 'super admin', 'admin', 'hq admin', 'hq_admin'])) {
                return 'Captain';
            }
            if (in_array($normalized, ['evac_admin', 'evac admin', 'evac_personnel', 'evac personnel', 'rescuer'])) {
                return 'Encoder';
            }
            if (in_array($normalized, ['household_resident', 'household resident'])) {
                return 'Household';
            }
        }
        return $name;
    }

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['role_name'] = $value;
    }

    public function getRoleNameAttribute(): ?string
    {
        return $this->getNameAttribute();
    }

    public function setRoleNameAttribute(?string $value): void
    {
        $this->attributes['role_name'] = $value;
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            public function select($columns = ['*'])
            {
                $columns = is_array($columns) ? $columns : func_get_args();
                foreach ($columns as &$column) {
                    if ($column === 'name') {
                        $column = 'role_name as name';
                    }
                }
                return parent::select($columns);
            }

            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if (is_array($column)) {
                    foreach ($column as $key => $val) {
                        $this->where($key, '=', $val, $boolean);
                    }
                    return $this;
                }
                if ($column === 'name') {
                    $column = 'role_key';
                }
                if ($column === 'role_key' || $column === 'role_name') {
                    $val = $value;
                    if ($value === null && $operator !== null) {
                        $val = $operator;
                    }

                    if (is_string($val)) {
                        $lowerVal = strtolower(trim($val));
                        $isNegation = in_array(trim((string)$operator), ['!=', '<>', 'not like'], true);

                        if ($lowerVal === 'captain') {
                            return $isNegation 
                                ? parent::whereNotIn('role_key', ['super_admin', 'admin'], $boolean)
                                : parent::whereIn('role_key', ['super_admin', 'admin'], $boolean);
                        }
                        if ($lowerVal === 'encoder') {
                            return $isNegation
                                ? parent::whereNotIn('role_key', ['evac_admin', 'evac_personnel', 'rescuer'], $boolean)
                                : parent::whereIn('role_key', ['evac_admin', 'evac_personnel', 'rescuer'], $boolean);
                        }
                        if ($lowerVal === 'household') {
                            return $isNegation
                                ? parent::whereNotIn('role_key', ['household_resident'], $boolean)
                                : parent::whereIn('role_key', ['household_resident'], $boolean);
                        }
                    }
                }
                return parent::where($column, $operator, $value, $boolean);
            }

            public function whereIn($column, $values, $boolean = 'and', $not = false)
            {
                if ($column === 'name') {
                    $column = 'role_key';
                }
                if (($column === 'role_key' || $column === 'role_name') && is_array($values)) {
                    $newValues = [];
                    foreach ($values as $val) {
                        $lowerVal = strtolower(trim($val));
                        if ($lowerVal === 'captain') {
                            $newValues[] = 'super_admin';
                            $newValues[] = 'admin';
                        } elseif ($lowerVal === 'encoder') {
                            $newValues[] = 'evac_admin';
                            $newValues[] = 'evac_personnel';
                            $newValues[] = 'rescuer';
                        } elseif ($lowerVal === 'household') {
                            $newValues[] = 'household_resident';
                        } else {
                            $newValues[] = $val;
                        }
                    }
                    $values = array_unique($newValues);
                    $column = 'role_key'; // query on key
                }
                return parent::whereIn($column, $values, $boolean, $not);
            }

            public function whereRaw($sql, $bindings = [], $boolean = 'and')
            {
                $sql = preg_replace('/\bname\b/i', 'role_key', $sql);
                if (is_array($bindings)) {
                    foreach ($bindings as &$binding) {
                        if (is_string($binding)) {
                            $lowerVal = strtolower(trim($binding));
                            if ($lowerVal === 'captain') {
                                $binding = 'admin';
                            } elseif ($lowerVal === 'encoder') {
                                $binding = 'evac_personnel';
                            } elseif ($lowerVal === 'household') {
                                $binding = 'household_resident';
                            }
                        }
                    }
                }
                return parent::whereRaw($sql, $bindings, $boolean);
            }

            public function orderBy($column, $direction = 'asc')
            {
                if ($column === 'name') {
                    $column = 'role_name';
                }
                return parent::orderBy($column, $direction);
            }
        };
    }
}
