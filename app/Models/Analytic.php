<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Analytic extends Model
{
    protected $fillable = [
        'barangay_id',
        'sitio',
        'total_households',
        'total_population',
        'total_pwd',
        'total_seniors'
    ];

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }
}
