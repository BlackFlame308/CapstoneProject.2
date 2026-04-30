<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    use HasUuids;

    use HasFactory;

    protected $fillable = ['region_id', 'name', 'code', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class)->orderBy('name');
    }
}
