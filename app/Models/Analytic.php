<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Analytic extends Model
{
    use HasUuids;

    protected $fillable = [
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
        'barangay_id'      => 'string',
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

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }
}

