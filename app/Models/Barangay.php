<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\NormalizesLocationNames;

class Barangay extends Model
{
    use HasFactory, NormalizesLocationNames;

    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->timestamps = false;
    }

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
        $normalized = static::normalizeLocationName($value);
        $this->attributes['barangay_name'] = $normalized;
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
        $columnMap = [
            'name' => 'barangay_name',
            'code' => 'barangay_code',
        ];

        return new class($query, $columnMap) extends \Illuminate\Database\Eloquent\Builder {
            protected $columnMap;

            public function __construct($query, $columnMap)
            {
                parent::__construct($query);
                $this->columnMap = $columnMap;
            }

            public function select($columns = ['*'])
            {
                $columns = is_array($columns) ? $columns : func_get_args();
                foreach ($columns as &$column) {
                    if ($column === 'name') {
                        $column = $this->columnMap['name'] . ' as name';
                    }
                    if ($column === 'code') {
                        $column = $this->columnMap['code'] . ' as code';
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

                $mappedCol = $this->columnMap[$column] ?? $column;

                if (func_num_args() === 2) {
                    $value = $operator;
                    $operator = '=';
                }

                if ($column === 'name' || $column === $this->columnMap['name']) {
                    if (is_string($value)) {
                        $value = \App\Models\Barangay::normalizeLocationName($value);
                        if ($operator === '=') {
                            $operator = 'like';
                        }
                    }
                }

                return parent::where($mappedCol, $operator, $value, $boolean);
            }

            public function whereIn($column, $values, $boolean = 'and', $not = false)
            {
                $mappedCol = $this->columnMap[$column] ?? $column;

                if ($column === 'name' || $column === $this->columnMap['name']) {
                    $values = collect($values)->map(function ($val) {
                        return is_string($val) ? \App\Models\Barangay::normalizeLocationName($val) : $val;
                    })->all();
                }

                return parent::whereIn($mappedCol, $values, $boolean, $not);
            }

            public function orderBy($column, $direction = 'asc')
            {
                $mappedCol = $this->columnMap[$column] ?? $column;
                return parent::orderBy($mappedCol, $direction);
            }
        };
    }
}
