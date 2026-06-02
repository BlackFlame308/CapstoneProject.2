<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $primaryKey = 'region_id';
    public $keyType = 'int';
    public $incrementing = false;

    protected $fillable = ['name', 'code', 'metadata', 'region_id'];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Region $region) {
            if (empty($region->region_id)) {
                $region->region_id = (static::max('region_id') ?? 0) + 1;
            }
        });
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['region_name'] ?? $this->attributes['name'] ?? null;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['region_name'] = $value;
    }

    public function getCodeAttribute(): ?string
    {
        return $this->attributes['region_code'] ?? $this->attributes['code'] ?? null;
    }

    public function setCodeAttribute($value): void
    {
        $this->attributes['region_code'] = $value;
    }

    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class, 'region_id', 'region_id')->orderBy('province_name');
    }

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            public function select($columns = ['*'])
            {
                $columns = is_array($columns) ? $columns : func_get_args();
                foreach ($columns as &$column) {
                    if ($column === 'name') {
                        $column = 'region_name as name';
                    }
                    if ($column === 'code') {
                        $column = 'region_code as code';
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
                if ($column === 'code') {
                    $column = 'region_code';
                }
                if ($column === 'name') {
                    $column = 'region_name';
                }
                return parent::where($column, $operator, $value, $boolean);
            }

            public function whereIn($column, $values, $boolean = 'and', $not = false)
            {
                if ($column === 'code') {
                    $column = 'region_code';
                }
                if ($column === 'name') {
                    $column = 'region_name';
                }
                return parent::whereIn($column, $values, $boolean, $not);
            }

            public function orderBy($column, $direction = 'asc')
            {
                if ($column === 'name') {
                    $column = 'region_name';
                }
                if ($column === 'code') {
                    $column = 'region_code';
                }
                return parent::orderBy($column, $direction);
            }
        };
    }
}
