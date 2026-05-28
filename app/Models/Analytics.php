<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Analytics extends Model
{
    protected $table = 'analytics';
    protected $primaryKey = 'analytic_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'analytic_id',
        'barangay_id',
        'purok_sitio',
        'record_period',
        'total_households',
        'total_population',
        'total_males',
        'total_females',
        'total_pwd',
        'total_seniors',
        'total_children',
        'total_adults',
        'total_pregnant',
        'total_evacuees',
    ];

    protected $casts = [
        'barangay_id'      => 'integer',
        'record_period'    => 'date',
        'total_households' => 'integer',
        'total_population' => 'integer',
        'total_males'      => 'integer',
        'total_females'    => 'integer',
        'total_pwd'        => 'integer',
        'total_seniors'    => 'integer',
        'total_children'   => 'integer',
        'total_adults'     => 'integer',
        'total_pregnant'   => 'integer',
        'total_evacuees'   => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Analytics $analytic) {
            $analytic->analytic_id ??= (string) Str::uuid();
        });
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id', 'barangay_id');
    }
}
