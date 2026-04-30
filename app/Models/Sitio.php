<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sitio extends Model
{
    use HasUuids;

    use HasFactory;

    protected $fillable = ['barangay_id', 'name', 'code', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}
