<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Household extends Model
{
    use HasUuids;

protected $fillable = [
        'household_code',
        'household_name',
        'email',
        'member_count',
        'address_id',
        'contact_number',
        'emergency_contact',
        'created_by',
    ];

protected $casts = [
        'address_id' => 'string',
        'created_by' => 'string',
    ];

    protected $appends = [
        'population',
        'vulnerable_count',
        'vulnerability_score',
        'vulnerability_badge',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Computed attributes ───────────────────────────────────────────────────

    public function getPopulationAttribute(): int
    {
        return $this->member_count > 0 ? $this->member_count : $this->members->count();
    }

    public function getVulnerableCountAttribute(): int
    {
        return $this->members->filter(function ($member) {
            return $member->is_pwd
                || $member->age < 18
                || $member->age >= 60;
        })->count();
    }

    public function getVulnerabilityScoreAttribute(): int
    {
        return $this->members->reduce(function ($score, $member) {
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

