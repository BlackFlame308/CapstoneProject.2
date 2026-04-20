<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    protected $fillable = ['city_id', 'name'];

    public $timestamps = false;

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function analytics()
    {
        return $this->hasMany(Analytic::class);
    }
}
