<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Member extends Model
{
    use HasUuids;

    protected $fillable = [
        'household_id',
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'birth_date',
        'sex',
        'gender',
        'age',
        'relation',
        'civil_status',
        'education_level',
        'occupation',
        'is_pwd',
        'is_pregnant',
        'special_needs',
        'is_graduate',
    ];

    protected $casts = [
        'birth_date'   => 'date',
        'is_pwd'       => 'boolean',
        'is_pregnant'  => 'boolean',
        'is_graduate'  => 'boolean',
        'age'          => 'integer',
    ];

    public $timestamps = true;

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    /**
     * Calculate age from birth_date.
     */
    public function getAgeAttribute(): int
    {
        return $this->birth_date?->age ?? ($this->attributes['age'] ?? 0);
    }

    /**
     * Full name convenience accessor.
     */
    public function getFullNameAttribute(): string
    {
        return trim(
            $this->first_name . ' ' .
            ($this->middle_name ? $this->middle_name . ' ' : '') .
            $this->last_name
        );
    }

    /**
     * Determine vulnerability category.
     */
    public function getVulnerabilityAttribute(): string
    {
        if ($this->is_pwd) return 'pwd';
        if ($this->age >= 60) return 'senior';
        if ($this->age < 18) return 'child';
        return 'adult';
    }
}
