<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    use HasFactory;

    protected $primaryKey = 'province_id';
    public $keyType = 'int';
    public $incrementing = true;

    protected $fillable = ['region_id', 'name', 'code', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'region_id');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'province_id', 'province_id')->orderBy('name');
    }
}
