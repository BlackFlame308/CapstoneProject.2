<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\DB;

/**
 * MemberAttributes Trait
 *
 * Handles name, age, sex/gender, relation, civil_status,
 * education_level, occupation, and special_needs accessors/mutators
 * for the Member model. Vulnerability attributes are in MemberVulnerabilityAttributes.
 */
trait MemberAttributes
{
    // ── Age & Name ────────────────────────────────────────────────────────────

    public function getAgeAttribute(): int
    {
        return $this->birth_date?->age ?? 0;
    }

    public function setAgeAttribute($value): void
    {
        // No-op — age is dynamically calculated from birth_date
    }

    public function getFullNameAttribute(): string
    {
        return trim(
            $this->first_name . ' ' .
            ($this->middle_name ? $this->middle_name . ' ' : '') .
            $this->last_name
        );
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['name'] ?? $this->full_name;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $value;
    }

    // ── Sex / Gender ─────────────────────────────────────────────────────────

    public function getSexAttribute(): ?string
    {
        $sex = $this->attributes['sex'] ?? null;
        if ($sex) return strtoupper($sex);

        $genderId = $this->attributes['gender_id'] ?? null;
        if ($genderId == 1) return 'M';
        if ($genderId == 2) return 'F';
        return null;
    }

    public function setSexAttribute($value): void
    {
        $val    = strtolower(trim((string) $value));
        $isMale = ($val === 'm' || $val === 'male');
        $this->attributes['sex']       = $isMale ? 'M' : 'F';
        $this->attributes['gender_id'] = $isMale ? 1 : 2;
    }

    public function getGenderAttribute(): ?string
    {
        return $this->attributes['gender'] ?? ($this->getSexAttribute() === 'M' ? 'Male' : 'Female');
    }

    public function setGenderAttribute($value): void
    {
        $this->setSexAttribute($value);
    }

    // ── Relation ─────────────────────────────────────────────────────────────

    public function getRelationAttribute(): ?string
    {
        $val = $this->attributes['relation'] ?? null;
        if (!$val) {
            $relId = $this->attributes['relationship_id'] ?? null;
            if ($relId) {
                $rel = DB::table('relationships')->where('relationship_id', $relId)->first();
                $val = $rel ? $rel->relationship_label : null;
            }
        }
        if ($val) {
            $lower = strtolower($val);
            if ($lower === 'head of household') return 'Head';
            if ($lower === 'other relative')    return 'Others';
            return $val;
        }
        return null;
    }

    public function setRelationAttribute($value): void
    {
        $val    = trim((string) $value);
        $mapped = match (strtolower($val)) {
            'head'          => 'Head of Household',
            'others', 'other', 'grandchild' => 'Other Relative',
            default         => $val,
        };
        $rel = DB::table('relationships')->where('relationship_label', 'like', $mapped)->first();
        if ($rel) {
            $this->attributes['relationship_id'] = $rel->relationship_id;
        }
        $this->attributes['relation'] = $val;
    }

    // ── Civil Status ─────────────────────────────────────────────────────────

    public function getCivilStatusAttribute(): ?string
    {
        $val = $this->attributes['civil_status'] ?? null;
        if (!$val && ($statusId = $this->attributes['civil_status_id'] ?? null)) {
            $status = DB::table('civil_statuses')->where('status_id', $statusId)->first();
            $val    = $status ? $status->status_label : null;
        }
        return $val;
    }

    public function setCivilStatusAttribute($value): void
    {
        $val    = trim((string) $value);
        $status = DB::table('civil_statuses')->where('status_label', 'like', $val)->first();
        if ($status) $this->attributes['civil_status_id'] = $status->status_id;
        $this->attributes['civil_status'] = $val;
    }

    // ── Education Level ───────────────────────────────────────────────────────

    public function getEducationLevelAttribute(): ?string
    {
        $val = $this->attributes['education_level'] ?? null;
        if (!$val && ($elId = $this->attributes['education_level_id'] ?? null)) {
            $el  = DB::table('education_levels')->where('education_level_id', $elId)->first();
            $val = $el ? $el->education_level_label : null;
        }
        return $val;
    }

    public function setEducationLevelAttribute($value): void
    {
        $val = trim((string) $value);
        $el  = DB::table('education_levels')->where('education_level_label', 'like', $val)->first();
        if ($el) $this->attributes['education_level_id'] = $el->education_level_id;
        $this->attributes['education_level'] = $val;
    }

    // ── Occupation ────────────────────────────────────────────────────────────

    public function getOccupationAttribute(): ?string
    {
        $val = $this->attributes['occupation'] ?? null;
        if (is_numeric($val)) {
            $occ = DB::table('occupations')->where('occuaption_id', (int) $val)->first();
            return $occ ? $occ->occupation_name : null;
        }
        return $val;
    }

    public function setOccupationAttribute($value): void
    {
        $val = trim((string) $value);
        if ($val === '') { $this->attributes['occupation'] = null; return; }

        $occ = DB::table('occupations')->where('occupation_name', 'like', $val)->first();
        if ($occ) { $this->attributes['occupation'] = $occ->occuaption_id; return; }

        try {
            $newId = (DB::table('occupations')->max('occuaption_id') ?? 0) + 1;
            DB::table('occupations')->insert(['occuaption_id' => $newId, 'occupation_name' => $val]);
            $this->attributes['occupation'] = $newId;
        } catch (\Throwable) {
            $this->attributes['occupation'] = $val;
        }
    }

    // ── Special Needs ─────────────────────────────────────────────────────────

    public function getSpecialNeedsAttribute(): ?string
    {
        return $this->attributes['special_needs'] ?? null;
    }

    public function setSpecialNeedsAttribute($value): void
    {
        $this->attributes['special_needs'] = $value;
    }
}
