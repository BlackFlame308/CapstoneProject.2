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
                $rawName = $role->attributes['role_name'] ?? $role->attributes['name'] ?? 'role';
                $role->role_key = \Illuminate\Support\Str::slug($rawName, '_');
            }
        });

        static::saving(function (Role $role) {
            $table = $role->getTable();
            if (!\Illuminate\Support\Facades\Schema::hasColumn($table, 'name')) {
                unset($role->attributes['name']);
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn($table, 'role_name')) {
                unset($role->attributes['role_name']);
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn($table, 'role_key')) {
                unset($role->attributes['role_key']);
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
            if (in_array($normalized, ['evac_admin', 'evac admin', 'moderator'])) {
                return 'Moderator';
            }
            if (in_array($normalized, ['evac_personnel', 'evac personnel', 'personnel', 'personel', 'rescuer'])) {
                return 'personel';
            }
            if ($normalized === 'encoder') {
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
        $this->attributes['name'] = $value;
    }

    public function getRoleNameAttribute(): ?string
    {
        return $this->getNameAttribute();
    }

    public function setRoleNameAttribute(?string $value): void
    {
        $this->attributes['role_name'] = $value;
        $this->attributes['name'] = $value;
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
                        
                        if ($column === 'role_key') {
                            $sluggedVal = \Illuminate\Support\Str::slug($lowerVal, '_');
                            if ($value === null) {
                                $operator = $sluggedVal;
                            } else {
                                $value = $sluggedVal;
                            }
                        }
                        
                        $isNegation = in_array(trim((string)$operator), ['!=', '<>', 'not like'], true);

                        if ($lowerVal === 'captain') {
                            return $isNegation 
                                ? parent::whereNotIn('role_key', ['super_admin', 'admin', 'captain'], $boolean)
                                : parent::whereIn('role_key', ['super_admin', 'admin', 'captain'], $boolean);
                        }
                        if ($lowerVal === 'encoder') {
                            return $isNegation
                                ? parent::whereNot('role_key', 'encoder', $boolean)
                                : parent::where('role_key', 'encoder', $boolean);
                        }
                        if ($lowerVal === 'moderator') {
                            return $isNegation
                                ? parent::whereNotIn('role_key', ['evac_admin', 'moderator'], $boolean)
                                : parent::whereIn('role_key', ['evac_admin', 'moderator'], $boolean);
                        }
                        if ($lowerVal === 'personnel' || $lowerVal === 'personel') {
                            return $isNegation
                                ? parent::whereNotIn('role_key', ['evac_personnel', 'personnel', 'personel', 'rescuer'], $boolean)
                                : parent::whereIn('role_key', ['evac_personnel', 'personnel', 'personel', 'rescuer'], $boolean);
                        }
                        if ($lowerVal === 'household') {
                            return $isNegation
                                ? parent::whereNotIn('role_key', ['household_resident', 'household'], $boolean)
                                : parent::whereIn('role_key', ['household_resident', 'household'], $boolean);
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
                            $newValues[] = 'captain';
                        } elseif ($lowerVal === 'encoder') {
                            $newValues[] = 'encoder';
                        } elseif ($lowerVal === 'moderator') {
                            $newValues[] = 'evac_admin';
                            $newValues[] = 'moderator';
                        } elseif ($lowerVal === 'personnel' || $lowerVal === 'personel') {
                            $newValues[] = 'evac_personnel';
                            $newValues[] = 'personnel';
                            $newValues[] = 'personel';
                            $newValues[] = 'rescuer';
                        } elseif ($lowerVal === 'household') {
                            $newValues[] = 'household_resident';
                            $newValues[] = 'household';
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
                    $isSqlite = config('database.default') === 'sqlite';
                    foreach ($bindings as &$bindingsValue) {
                        if (is_string($bindingsValue)) {
                            $lowerVal = strtolower(trim($bindingsValue));
                            if ($lowerVal === 'captain') {
                                $bindingsValue = 'admin';
                            } elseif ($lowerVal === 'encoder') {
                                $bindingsValue = 'encoder';
                            } elseif ($lowerVal === 'moderator') {
                                $bindingsValue = $isSqlite ? 'moderator' : 'evac_admin';
                            } elseif ($lowerVal === 'personnel' || $lowerVal === 'personel') {
                                $bindingsValue = $isSqlite ? 'personel' : 'evac_personnel';
                            } elseif ($lowerVal === 'household') {
                                $bindingsValue = $isSqlite ? 'household' : 'household_resident';
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
