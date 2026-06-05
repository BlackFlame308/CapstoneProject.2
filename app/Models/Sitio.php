<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\NormalizesLocationNames;

class Sitio extends Model
{
    use HasFactory, NormalizesLocationNames;

    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->timestamps = (config('database.default') === 'sqlite');
    }

    protected $primaryKey = 'sitio_id';
    public $keyType = 'int';
    public $incrementing = false;

    protected $fillable = ['barangay_id', 'name', 'code', 'metadata', 'sitio_id'];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Sitio $sitio) {
            if (empty($sitio->sitio_id)) {
                $sitio->sitio_id = (static::max('sitio_id') ?? 0) + 1;
            }
        });
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['sitio_name'] ?? $this->attributes['name'] ?? null;
    }

    public function setNameAttribute($value): void
    {
        $normalized = static::normalizeLocationName($value);
        if (config('database.default') === 'sqlite') {
            $this->attributes['name'] = $normalized;
        } else {
            $this->attributes['sitio_name'] = $normalized;
        }
    }

    public function getCodeAttribute(): ?string
    {
        if (config('database.default') === 'sqlite') {
            return $this->attributes['code'] ?? null;
        }
        return null;
    }

    public function setCodeAttribute($value): void
    {
        if (config('database.default') === 'sqlite') {
            $this->attributes['code'] = $value;
        }
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id', 'barangay_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'barangay_id', 'barangay_id');
    }

    public function newEloquentBuilder($query)
    {
        $isSqlite = (config('database.default') === 'sqlite');
        $columnMap = [
            'name' => $isSqlite ? 'name' : 'sitio_name',
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
                        $value = \App\Models\Sitio::normalizeLocationName($value);
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
                        return is_string($val) ? \App\Models\Sitio::normalizeLocationName($val) : $val;
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
