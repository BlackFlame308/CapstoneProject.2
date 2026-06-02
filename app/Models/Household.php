<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Household extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'household_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'household_id',
        'household_code',
        'household_number',
        'household_name',
        'email',
        'member_count',
        'address_id',
        'contact_number',
        'emergency_contact',
        'created_by',
    ];

    protected $casts = [
        'address_id'   => 'integer',
        'member_count' => 'integer',
        'created_by'   => 'string',
    ];

    protected $appends = [
        'population',
        'vulnerable_count',
        'vulnerability_score',
        'vulnerability_badge',
    ];



    protected static function booted(): void
    {
        static::creating(function (Household $household) {
            $household->household_id ??= $household->household_code ?: static::generateHouseholdId();
            $household->household_code ??= $household->household_id;
        });
    }

    public static function generateHouseholdId(): string
    {
        do {
            $id = 'HH' . random_int(100000, 999999);
        } while (static::where('household_id', $id)->orWhere('household_code', $id)->exists());

        return $id;
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id', 'address_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'household_id', 'household_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'household_id', 'household_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    // ── Computed attributes ───────────────────────────────────────────────────

    public function getPopulationAttribute(): int
    {
        return $this->member_count > 0 ? $this->member_count : $this->members()->count();
    }

    public function getVulnerableCountAttribute(): int
    {
        $members = $this->relationLoaded('members') ? $this->members : $this->members()->get();

        return $members->filter(function ($member) {
            return $member->is_pwd
                || $member->age < 18
                || $member->age >= 60;
        })->count();
    }

    public function getVulnerabilityScoreAttribute(): int
    {
        $members = $this->relationLoaded('members') ? $this->members : $this->members()->get();

        return $members->reduce(function ($score, $member) {
            if ($member->is_pwd)    $score += 4;
            if ($member->age >= 60) $score += 2;
            if ($member->age < 18)  $score += 1;
            return $score;
        }, 0);
    }

    public function getVulnerabilityBadgeAttribute(): string
    {
        $score = $this->vulnerability_score;
        if ($score > 7) return 'Critical';
        if ($score > 4) return 'High';
        return 'Moderate';
    }
}
