<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $primaryKey = 'province_id';
    public $keyType = 'int';
    public $incrementing = false;

    protected $fillable = ['region_id', 'name', 'code', 'metadata', 'province_id'];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Province $province) {
            if (empty($province->province_id)) {
                $province->province_id = (static::max('province_id') ?? 0) + 1;
            }
        });
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['province_name'] ?? $this->attributes['name'] ?? null;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['province_name'] = $value;
    }

    public function getCodeAttribute(): ?string
    {
        return $this->attributes['province_code'] ?? $this->attributes['code'] ?? null;
    }

    public function setCodeAttribute($value): void
    {
        $this->attributes['province_code'] = $value;
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'region_id');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'province_id', 'province_id')->orderBy('city_name');
    }

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            public function select($columns = ['*'])
            {
                $columns = is_array($columns) ? $columns : func_get_args();
                foreach ($columns as &$column) {
                    if ($column === 'name') {
                        $column = 'province_name as name';
                    }
                    if ($column === 'code') {
                        $column = 'province_code as code';
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
                    $column = 'province_code';
                }
                if ($column === 'name') {
                    $column = 'province_name';
                }
                return parent::where($column, $operator, $value, $boolean);
            }

            public function whereIn($column, $values, $boolean = 'and', $not = false)
            {
                if ($column === 'code') {
                    $column = 'province_code';
                }
                if ($column === 'name') {
                    $column = 'province_name';
                }
                return parent::whereIn($column, $values, $boolean, $not);
            }

            public function orderBy($column, $direction = 'asc')
            {
                if ($column === 'name') {
                    $column = 'province_name';
                }
                if ($column === 'code') {
                    $column = 'province_code';
                }
                return parent::orderBy($column, $direction);
            }
        };
    }
}
