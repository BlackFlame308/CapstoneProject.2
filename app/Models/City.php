<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $primaryKey = 'city_id';
    public $keyType = 'int';
    public $incrementing = false;

    protected $fillable = ['province_id', 'name', 'code', 'metadata', 'city_id'];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (City $city) {
            if (empty($city->city_id)) {
                $city->city_id = (static::max('city_id') ?? 0) + 1;
            }
        });
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['city_name'] ?? $this->attributes['name'] ?? null;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['city_name'] = $value;
    }

    public function getCodeAttribute(): ?string
    {
        return $this->attributes['city_code'] ?? $this->attributes['code'] ?? null;
    }

    public function setCodeAttribute($value): void
    {
        $this->attributes['city_code'] = $value;
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }

    public function barangays(): HasMany
    {
        return $this->hasMany(Barangay::class, 'city_id', 'city_id')->orderBy('barangay_name');
    }

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            public function select($columns = ['*'])
            {
                $columns = is_array($columns) ? $columns : func_get_args();
                foreach ($columns as &$column) {
                    if ($column === 'name') {
                        $column = 'city_name as name';
                    }
                    if ($column === 'code') {
                        $column = 'city_code as code';
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
                    $column = 'city_code';
                }
                if ($column === 'name') {
                    $column = 'city_name';
                }
                return parent::where($column, $operator, $value, $boolean);
            }

            public function whereIn($column, $values, $boolean = 'and', $not = false)
            {
                if ($column === 'code') {
                    $column = 'city_code';
                }
                if ($column === 'name') {
                    $column = 'city_name';
                }
                return parent::whereIn($column, $values, $boolean, $not);
            }

            public function orderBy($column, $direction = 'asc')
            {
                if ($column === 'name') {
                    $column = 'city_name';
                }
                if ($column === 'code') {
                    $column = 'city_code';
                }
                return parent::orderBy($column, $direction);
            }
        };
    }
}
