<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = ['street', 'purok', 'barangay_id'];

    public $timestamps = false;

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function households()
    {
        return $this->hasMany(Household::class);
    }
}
