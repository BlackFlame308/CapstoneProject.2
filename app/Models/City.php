<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $primaryKey = 'city_id';
    public $keyType = 'int';
    public $incrementing = true;

    protected $fillable = ['province_id', 'name', 'code', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }

    public function barangays(): HasMany
    {
        return $this->hasMany(Barangay::class, 'city_id', 'city_id')->orderBy('name');
    }
}
