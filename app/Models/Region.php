<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    use HasFactory;

    protected $primaryKey = 'region_id';
    public $keyType = 'int';
    public $incrementing = true;

    protected $fillable = ['name', 'code', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class, 'region_id', 'region_id')->orderBy('name');
    }
}
