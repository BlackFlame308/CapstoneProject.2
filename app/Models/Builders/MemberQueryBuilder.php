<?php

namespace App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * MemberQueryBuilder
 *
 * Custom Eloquent query builder for the Member model.
 * Provides transparent query translation for virtual columns
 * such as is_pwd, is_pregnant, is_senior, sex, relation,
 * civil_status, and education_level.
 */
class MemberQueryBuilder extends Builder
{
    public function whereRaw($sql, $bindings = [], $boolean = 'and')
    {
        if (preg_match('/LOWER\(sex\)\s+IN\s+\(\'m\'\s*,\s*\'male\'\)/i', $sql)) {
            return $this->where('gender_id', 1, null, $boolean);
        }
        if (preg_match('/LOWER\(sex\)\s+IN\s+\(\'f\'\s*,\s*\'female\'\)/i', $sql)) {
            return $this->where('gender_id', 2, null, $boolean);
        }
        return parent::whereRaw($sql, $bindings, $boolean);
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if (is_array($column)) {
            foreach ($column as $key => $val) {
                $this->where($key, '=', $val, $boolean);
            }
            return $this;
        }

        // Normalize parameters for 2-argument calls (e.g. where('relation', 'Head'))
        // exactly like Laravel's Eloquent Builder does.
        if (func_num_args() === 2) {
            $value    = $operator;
            $operator = '=';
        }

        return match ($column) {
            'is_pwd'           => $this->applyVulnerabilityFilter('pwd', $operator, $value, $boolean),
            'is_pregnant'      => $this->applyVulnerabilityFilter('pregnant', $operator, $value, $boolean),
            'is_senior'        => $this->applySeniorFilter($operator, $value, $boolean),
            'sex'              => $this->applySexFilter($operator, $value, $boolean),
            'relation'         => $this->applyRelationFilter($operator, $value, $boolean),
            'civil_status'     => $this->applyCivilStatusFilter($operator, $value, $boolean),
            'education_level'  => $this->applyEducationLevelFilter($operator, $value, $boolean),
            default            => parent::where($column, $operator, $value, $boolean),
        };
    }

    // ── Private filter helpers ────────────────────────────────────────────────

    private function applyVulnerabilityFilter(string $key, $operator, $value, string $boolean): static
    {
        return $this->whereExists(function ($q) use ($key) {
            $q->select(DB::raw(1))
              ->from('member_vulnerable_groups')
              ->join('vulnerable_groups', 'member_vulnerable_groups.vulnerable_group_id', '=', 'vulnerable_groups.vulnerable_group_id')
              ->whereColumn('member_vulnerable_groups.member_id', 'household_members.member_id')
              ->where('vulnerable_groups.vulnerable_group_key', $key);
        }, $boolean, $value ? false : true);
    }

    private function applySeniorFilter($operator, $value, string $boolean): static
    {
        $ageLimit = now()->subYears(60)->format('Y-m-d');
        return $this->where('birth_date', $value ? '<=' : '>', $ageLimit, $boolean);
    }

    private function applySexFilter($operator, $value, string $boolean): static
    {
        $v        = strtolower(trim((string) $value));
        $genderId = ($v === 'm' || $v === 'male') ? 1 : 2;
        return $this->where('gender_id', '=', $genderId, $boolean);
    }


    private function applyRelationFilter($operator, $value, string $boolean): static
    {
        $val   = trim((string) $value);
        $lower = strtolower($val);

        $mappedLabel = match ($lower) {
            'head of household', 'head', 'household head', 'leader' => 'Head of Household',
            'spouse', 'husband', 'wife', 'partner' => 'Spouse',
            'child', 'son', 'daughter', 'kid', 'kids', 'baby' => 'Child',
            'parent', 'father', 'mother', 'dad', 'mom', 'papa', 'mama' => 'Parent',
            'sibling', 'sibing', 'siblings', 'brother', 'sister', 'bro', 'sis' => 'Sibling',
            'grandchild', 'grandson', 'granddaughter', 'other relative', 'others', 'other', 'relative', 'cousin', 'aunt', 'uncle', 'nephew', 'niece', 'in-law' => 'Other Relative',
            default => $val,
        };

        $rel = DB::table('relationships')->where('relationship_label', 'like', $mappedLabel)->first();
        if (!$rel) {
            $rel = DB::table('relationships')->where('relationship_key', 'like', $lower)->first();
        }
        $relId = $rel ? $rel->relationship_id : 0;
        return $this->where('relationship_id', '=', $relId, $boolean);
    }

    private function applyCivilStatusFilter($operator, $value, string $boolean): static
    {
        $status   = DB::table('civil_statuses')->where('status_label', 'like', $value)->first();
        $statusId = $status ? $status->status_id : 0;
        return $this->where('civil_status_id', '=', $statusId, $boolean);
    }

    private function applyEducationLevelFilter($operator, $value, string $boolean): static
    {
        $el   = DB::table('education_levels')->where('education_level_label', 'like', $value)->first();
        $elId = $el ? $el->education_level_id : 0;
        return $this->where('education_level_id', '=', $elId, $boolean);
    }
}
