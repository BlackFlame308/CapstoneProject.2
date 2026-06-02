<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sitio extends Model
{
    use HasFactory;

    public $timestamps = false;

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
        $this->attributes['sitio_name'] = $value;
    }

    public function getCodeAttribute(): ?string
    {
        return null;
    }

    public function setCodeAttribute($value): void
    {
        // No-op, column absent in database
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
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            public function select($columns = ['*'])
            {
                $columns = is_array($columns) ? $columns : func_get_args();
                foreach ($columns as &$column) {
                    if ($column === 'name') {
                        $column = 'sitio_name as name';
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
                    $column = 'sitio_name';
                }
                return parent::where($column, $operator, $value, $boolean);
            }

            public function whereIn($column, $values, $boolean = 'and', $not = false)
            {
                if ($column === 'name') {
                    $column = 'sitio_name';
                }
                return parent::whereIn($column, $values, $boolean, $not);
            }

            public function orderBy($column, $direction = 'asc')
            {
                if ($column === 'name') {
                    $column = 'sitio_name';
                }
                return parent::orderBy($column, $direction);
            }
        };
    }
}
