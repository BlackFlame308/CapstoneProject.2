<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasUuids;

    use HasFactory;

    protected $fillable = ['province_id', 'name', 'code', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function barangays(): HasMany
    {
        return $this->hasMany(Barangay::class)->orderBy('name');
    }
}
