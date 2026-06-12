<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Member extends Model
{
    use SoftDeletes;

    public ?bool $tempIsPwd = null;
    public ?bool $tempIsPregnant = null;

    protected $table = 'household_members';
    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable('household_members');
        $this->timestamps = false;
    }

    protected $primaryKey = 'member_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'member_id',
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
        'special_needs',
        'is_graduate',
        'gender_id',
        'relationship_id',
        'civil_status_id',
        'education_level_id',
        'is_pwd',
        'is_pregnant',
    ];

    protected $casts = [
        'birth_date'   => 'date',
        'is_pwd'       => 'boolean',
        'is_senior'    => 'boolean',
        'is_pregnant'  => 'boolean',
        'is_graduate'  => 'boolean',
        'age'          => 'integer',
        'gender_id'    => 'integer',
        'relationship_id' => 'integer',
        'civil_status_id' => 'integer',
        'education_level_id' => 'integer',
    ];

    protected $appends = [
        'full_name',
        'vulnerability',
    ];

    protected static function booted(): void
    {
        static::creating(function (Member $member) {
            $member->member_id ??= (string) Str::uuid();
        });

        static::saved(function (Member $member) {
            // Sync PWD vulnerability group
            if ($member->tempIsPwd !== null) {
                $isPwd = $member->tempIsPwd;
                $pwdGroup = \Illuminate\Support\Facades\DB::table('vulnerable_groups')->where('vulnerable_group_key', 'pwd')->first();
                if ($pwdGroup) {
                    if ($isPwd) {
                        \Illuminate\Support\Facades\DB::table('member_vulnerable_groups')->insertOrIgnore([
                            'member_id' => $member->member_id,
                            'vulnerable_group_id' => $pwdGroup->vulnerable_group_id
                        ]);
                    } else {
                        \Illuminate\Support\Facades\DB::table('member_vulnerable_groups')
                            ->where('member_id', $member->member_id)
                            ->where('vulnerable_group_id', $pwdGroup->vulnerable_group_id)
                            ->delete();
                    }
                }
            }

            // Sync Pregnant vulnerability group
            if ($member->tempIsPregnant !== null) {
                $isPregnant = $member->tempIsPregnant;
                $pregGroup = \Illuminate\Support\Facades\DB::table('vulnerable_groups')->where('vulnerable_group_key', 'pregnant')->first();
                if ($pregGroup) {
                    if ($isPregnant) {
                        \Illuminate\Support\Facades\DB::table('member_vulnerable_groups')->insertOrIgnore([
                            'member_id' => $member->member_id,
                            'vulnerable_group_id' => $pregGroup->vulnerable_group_id
                        ]);
                    } else {
                        \Illuminate\Support\Facades\DB::table('member_vulnerable_groups')
                            ->where('member_id', $member->member_id)
                            ->where('vulnerable_group_id', $pregGroup->vulnerable_group_id)
                            ->delete();
                    }
                }
            }
        });

        static::deleting(function (Member $member) {
            $occId = $member->attributes['occupation'] ?? null;
            if ($occId && is_numeric($occId)) {
                try {
                    \Illuminate\Support\Facades\DB::table('occupations')
                        ->where('occuaption_id', $occId)
                        ->where('occupation_name', 'Teacher')
                        ->delete();
                } catch (\Throwable $e) {}
            }
        });
    }

    public function household()
    {
        return $this->belongsTo(Household::class, 'household_id', 'household_id');
    }

    public function getAgeAttribute(): int
    {
        return $this->birth_date?->age ?? 0;
    }

    public function setAgeAttribute($value): void
    {
        // No-op, age is dynamically calculated from birth_date
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

    public function getVulnerabilityAttribute(): string
    {
        if ($this->is_pwd) return 'pwd';
        if ($this->age >= 60) return 'senior';
        if ($this->age < 18) return 'child';
        return 'adult';
    }

    public function getIsPwdAttribute(): bool
    {
        if ($this->tempIsPwd !== null) {
            return $this->tempIsPwd;
        }

        return \Illuminate\Support\Facades\DB::table('member_vulnerable_groups')
            ->join('vulnerable_groups', 'member_vulnerable_groups.vulnerable_group_id', '=', 'vulnerable_groups.vulnerable_group_id')
            ->where('member_vulnerable_groups.member_id', $this->member_id)
            ->where('vulnerable_groups.vulnerable_group_key', 'pwd')
            ->exists();
    }

    public function setIsPwdAttribute($value): void
    {
        $this->tempIsPwd = (bool)$value;
    }

    public function getIsSeniorAttribute(): bool
    {
        return $this->age >= 60;
    }

    public function setIsSeniorAttribute($value): void
    {
        // Dynamic based on age, no-op
    }

    public function getIsPregnantAttribute(): bool
    {
        if ($this->tempIsPregnant !== null) {
            return $this->tempIsPregnant;
        }

        return \Illuminate\Support\Facades\DB::table('member_vulnerable_groups')
            ->join('vulnerable_groups', 'member_vulnerable_groups.vulnerable_group_id', '=', 'vulnerable_groups.vulnerable_group_id')
            ->where('member_vulnerable_groups.member_id', $this->member_id)
            ->where('vulnerable_groups.vulnerable_group_key', 'pregnant')
            ->exists();
    }

    public function setIsPregnantAttribute($value): void
    {
        $this->tempIsPregnant = (bool)$value;
    }

    public function getSexAttribute(): ?string
    {
        $sex = $this->attributes['sex'] ?? null;
        if ($sex) {
            return strtoupper($sex); // returns M or F
        }
        $genderId = $this->attributes['gender_id'] ?? null;
        if ($genderId == 1) return 'M';
        if ($genderId == 2) return 'F';
        return null;
    }

    public function setSexAttribute($value): void
    {
        $val = strtolower(trim((string)$value));
        $genderId = ($val === 'm' || $val === 'male') ? 1 : 2;
        $this->attributes['gender_id'] = $genderId;
    }

    public function getGenderAttribute(): ?string
    {
        $gender = $this->attributes['gender'] ?? null;
        if ($gender) {
            return $gender;
        }
        return $this->getSexAttribute() === 'M' ? 'Male' : 'Female';
    }

    public function setGenderAttribute($value): void
    {
        $this->setSexAttribute($value);
    }

    public function getRelationAttribute(): ?string
    {
        $val = $this->attributes['relation'] ?? null;
        if (!$val) {
            $relId = $this->attributes['relationship_id'] ?? null;
            if ($relId) {
                $rel = \Illuminate\Support\Facades\DB::table('relationships')->where('relationship_id', $relId)->first();
                $val = $rel ? $rel->relationship_label : null;
            }
        }

        if ($val) {
            if (strtolower($val) === 'head of household') {
                return 'Head';
            }
            if (strtolower($val) === 'other relative') {
                return 'Others';
            }
            return $val;
        }

        return null;
    }

    public function setRelationAttribute($value): void
    {
        $val = trim((string)$value);

        $mapped = $val;
        if (strtolower($val) === 'head') {
            $mapped = 'Head of Household';
        } elseif (strtolower($val) === 'others' || strtolower($val) === 'other' || strtolower($val) === 'grandchild') {
            $mapped = 'Other Relative';
        }

        $rel = \Illuminate\Support\Facades\DB::table('relationships')
            ->where('relationship_label', 'like', $mapped)
            ->first();
        if ($rel) {
            $this->attributes['relationship_id'] = $rel->relationship_id;
        }
    }

    public function getCivilStatusAttribute(): ?string
    {
        $val = $this->attributes['civil_status'] ?? null;
        if (!$val) {
            $statusId = $this->attributes['civil_status_id'] ?? null;
            if ($statusId) {
                $status = \Illuminate\Support\Facades\DB::table('civil_statuses')->where('status_id', $statusId)->first();
                $val = $status ? $status->status_label : null;
            }
        }
        return $val;
    }

    public function setCivilStatusAttribute($value): void
    {
        $val = trim((string)$value);

        $status = \Illuminate\Support\Facades\DB::table('civil_statuses')
            ->where('status_label', 'like', $val)
            ->first();
        if ($status) {
            $this->attributes['civil_status_id'] = $status->status_id;
        }
    }

    public function getEducationLevelAttribute(): ?string
    {
        $val = $this->attributes['education_level'] ?? null;
        if (!$val) {
            $elId = $this->attributes['education_level_id'] ?? null;
            if ($elId) {
                $el = \Illuminate\Support\Facades\DB::table('education_levels')->where('education_level_id', $elId)->first();
                $val = $el ? $el->education_level_label : null;
            }
        }
        return $val;
    }

    public function setEducationLevelAttribute($value): void
    {
        $val = trim((string)$value);

        $el = \Illuminate\Support\Facades\DB::table('education_levels')
            ->where('education_level_label', 'like', $val)
            ->first();
        if ($el) {
            $this->attributes['education_level_id'] = $el->education_level_id;
        }
    }

    public function getOccupationAttribute(): ?string
    {
        $val = $this->attributes['occupation'] ?? null;
        if (is_numeric($val)) {
            $occ = \Illuminate\Support\Facades\DB::table('occupations')->where('occuaption_id', (int)$val)->first();
            return $occ ? $occ->occupation_name : null;
        }
        return $val;
    }

    public function setOccupationAttribute($value): void
    {
        $val = trim((string)$value);
        $this->attributes['occupation'] = $val;

        if (empty($val)) {
            return;
        }

        $occ = \Illuminate\Support\Facades\DB::table('occupations')->where('occupation_name', 'like', $val)->first();
        if ($occ) {
            $this->attributes['occupation'] = $occ->occuaption_id;
        } else {
            try {
                $maxId = \Illuminate\Support\Facades\DB::table('occupations')->max('occuaption_id') ?? 0;
                $newId = $maxId + 1;
                \Illuminate\Support\Facades\DB::table('occupations')->insert([
                    'occuaption_id' => $newId,
                    'occupation_name' => $val
                ]);
                $this->attributes['occupation'] = $newId;
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }

    public function getSpecialNeedsAttribute(): ?string
    {
        return $this->attributes['special_needs'] ?? null;
    }

    public function setSpecialNeedsAttribute($value): void
    {
        $this->attributes['special_needs'] = $value;
    }

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
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

                if ($column === 'is_pwd') {
                    $val = func_num_args() === 2 ? $operator : $value;
                    $boolean = func_num_args() === 4 ? $boolean : 'and';
                    return $this->whereExists(function($q) {
                        $q->select(\Illuminate\Support\Facades\DB::raw(1))
                            ->from('member_vulnerable_groups')
                            ->join('vulnerable_groups', 'member_vulnerable_groups.vulnerable_group_id', '=', 'vulnerable_groups.vulnerable_group_id')
                            ->whereColumn('member_vulnerable_groups.member_id', 'household_members.member_id')
                            ->where('vulnerable_groups.vulnerable_group_key', 'pwd');
                    }, $boolean, $val ? false : true);
                }

                if ($column === 'is_pregnant') {
                    $val = func_num_args() === 2 ? $operator : $value;
                    $boolean = func_num_args() === 4 ? $boolean : 'and';
                    return $this->whereExists(function($q) {
                        $q->select(\Illuminate\Support\Facades\DB::raw(1))
                            ->from('member_vulnerable_groups')
                            ->join('vulnerable_groups', 'member_vulnerable_groups.vulnerable_group_id', '=', 'vulnerable_groups.vulnerable_group_id')
                            ->whereColumn('member_vulnerable_groups.member_id', 'household_members.member_id')
                            ->where('vulnerable_groups.vulnerable_group_key', 'pregnant');
                    }, $boolean, $val ? false : true);
                }

                if ($column === 'is_senior') {
                    $val = func_num_args() === 2 ? $operator : $value;
                    $boolean = func_num_args() === 4 ? $boolean : 'and';
                    $ageLimit = now()->subYears(60)->format('Y-m-d');
                    return $this->where('birth_date', $val ? '<=' : '>', $ageLimit, $boolean);
                }

                if ($column === 'sex') {
                    $val = func_num_args() === 2 ? $operator : $value;
                    $boolean = func_num_args() === 4 ? $boolean : 'and';
                    $v = strtolower(trim((string)$val));
                    $genderId = ($v === 'm' || $v === 'male') ? 1 : 2;
                    return $this->where('gender_id', '=', $genderId, $boolean);
                }

                if ($column === 'relation') {
                    $val = func_num_args() === 2 ? $operator : $value;
                    $boolean = func_num_args() === 4 ? $boolean : 'and';
                    $mapped = trim((string)$val);
                    if (strtolower($mapped) === 'head') {
                        $mapped = 'Head of Household';
                    } elseif (strtolower($mapped) === 'others' || strtolower($mapped) === 'other' || strtolower($mapped) === 'grandchild') {
                        $mapped = 'Other Relative';
                    }
                    $rel = \Illuminate\Support\Facades\DB::table('relationships')
                        ->where('relationship_label', 'like', $mapped)
                        ->first();
                    $relId = $rel ? $rel->relationship_id : 0;
                    return $this->where('relationship_id', '=', $relId, $boolean);
                }

                if ($column === 'civil_status') {
                    $val = func_num_args() === 2 ? $operator : $value;
                    $boolean = func_num_args() === 4 ? $boolean : 'and';
                    $status = \Illuminate\Support\Facades\DB::table('civil_statuses')->where('status_label', 'like', $val)->first();
                    $statusId = $status ? $status->status_id : 0;
                    return $this->where('civil_status_id', '=', $statusId, $boolean);
                }

                if ($column === 'education_level') {
                    $val = func_num_args() === 2 ? $operator : $value;
                    $boolean = func_num_args() === 4 ? $boolean : 'and';
                    $el = \Illuminate\Support\Facades\DB::table('education_levels')->where('education_level_label', 'like', $val)->first();
                    $elId = $el ? $el->education_level_id : 0;
                    return $this->where('education_level_id', '=', $elId, $boolean);
                }

                return parent::where($column, $operator, $value, $boolean);
            }
        };
    }
}
