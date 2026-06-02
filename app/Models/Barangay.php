<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barangay extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $primaryKey = 'barangay_id';
    public $keyType = 'int';
    public $incrementing = false;

    protected $fillable = ['city_id', 'name', 'code', 'metadata', 'barangay_id'];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Barangay $barangay) {
            if (empty($barangay->barangay_id)) {
                $barangay->barangay_id = (static::max('barangay_id') ?? 0) + 1;
            }
        });
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['barangay_name'] ?? $this->attributes['name'] ?? null;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['barangay_name'] = $value;
    }

    public function getCodeAttribute(): ?string
    {
        return $this->attributes['barangay_code'] ?? $this->attributes['code'] ?? null;
    }

    public function setCodeAttribute($value): void
    {
        $this->attributes['barangay_code'] = $value;
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id', 'city_id');
    }

    public function sitios(): HasMany
    {
        return $this->hasMany(Sitio::class, 'barangay_id', 'barangay_id')->orderBy('sitio_name');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'barangay_id', 'barangay_id');
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(Analytics::class, 'barangay_id', 'barangay_id');
    }

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            public function select($columns = ['*'])
            {
                $columns = is_array($columns) ? $columns : func_get_args();
                foreach ($columns as &$column) {
                    if ($column === 'name') {
                        $column = 'barangay_name as name';
                    }
                    if ($column === 'code') {
                        $column = 'barangay_code as code';
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
                    $column = 'barangay_code';
                }
                if ($column === 'name') {
                    $column = 'barangay_name';
                }
                return parent::where($column, $operator, $value, $boolean);
            }

            public function whereIn($column, $values, $boolean = 'and', $not = false)
            {
                if ($column === 'code') {
                    $column = 'barangay_code';
                }
                if ($column === 'name') {
                    $column = 'barangay_name';
                }
                return parent::whereIn($column, $values, $boolean, $not);
            }

            public function orderBy($column, $direction = 'asc')
            {
                if ($column === 'name') {
                    $column = 'barangay_name';
                }
                if ($column === 'code') {
                    $column = 'barangay_code';
                }
                return parent::orderBy($column, $direction);
            }
        };
    }
}
