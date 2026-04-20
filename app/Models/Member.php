<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'household_id',
        'first_name',
        'middle_name',
        'last_name',
        'birth_date',
        'sex',
        'civil_status',
        'education_level',
        'profession',
        'is_pwd'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_pwd' => 'boolean'
    ];

    public $timestamps = false;

    public function household()
    {
        return $this->belongsTo(Household::class);
    }
}
