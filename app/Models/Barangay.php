<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barangay extends Model
{
    use HasUuids;

    use HasFactory;

    protected $fillable = ['city_id', 'name', 'code', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function sitios(): HasMany
    {
        return $this->hasMany(Sitio::class)->orderBy('name');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(Analytics::class);
    }
}
