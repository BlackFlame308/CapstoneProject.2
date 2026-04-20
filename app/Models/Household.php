<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Household extends Model
{
    protected $fillable = [
        'household_code',
        'address_id',
        'contact_number',
        'emergency_contact',
        'created_by'
    ];

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
