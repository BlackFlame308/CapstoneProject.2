<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barangay extends Model
{
    use HasFactory;

    protected $primaryKey = 'barangay_id';
    public $keyType = 'int';
    public $incrementing = true;

    protected $fillable = ['city_id', 'name', 'code', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id', 'city_id');
    }

    public function sitios(): HasMany
    {
        return $this->hasMany(Sitio::class, 'barangay_id', 'barangay_id')->orderBy('name');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'barangay_id', 'barangay_id');
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(Analytics::class, 'barangay_id', 'barangay_id');
    }
}
