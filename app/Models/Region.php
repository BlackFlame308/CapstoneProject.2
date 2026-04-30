<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    use HasUuids;

    use HasFactory;

    protected $fillable = ['name', 'code', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class)->orderBy('name');
    }
}
