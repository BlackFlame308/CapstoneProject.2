<?php

namespace App\Models;

use App\Models\Builders\MemberQueryBuilder;
use App\Models\Traits\MemberAttributes;
use App\Models\Traits\MemberVulnerabilityAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Member Model
 * 
 * Represents a single member/resident inside a household (table: household_members).
 * 
 * CLEAN CODE DELEGATION STRUCTURE:
 * 1. Virtual Attributes / Accessors / Mutators: Delegated to traits to keep this file slim:
 *    - Traits\MemberAttributes (for name, age, relation, civil_status, occupation, gender, etc.)
 *    - Traits\MemberVulnerabilityAttributes (for PWD, pregnancy, and senior checks)
 * 2. Scopes and Query Filters: Delegated to:
 *    - Builders\MemberQueryBuilder (intercepts SQL filters like where('relation', 'Head') and automatically maps them to relational IDs)
 */
class Member extends Model
{
    use SoftDeletes;
    use MemberAttributes;
    use MemberVulnerabilityAttributes;

    /** @var bool|null Temporary flag used during save to sync PWD vulnerability group */
    public ?bool $tempIsPwd = null;

    /** @var bool|null Temporary flag used during save to sync Pregnant vulnerability group */
    public ?bool $tempIsPregnant = null;

    protected $table      = 'household_members';
    public    $timestamps = false;

    protected $primaryKey = 'member_id';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'member_id', 'household_id', 'name',
        'first_name', 'middle_name', 'last_name',
        'birth_date', 'sex', 'gender', 'age',
        'relation', 'civil_status', 'education_level',
        'occupation', 'special_needs', 'is_graduate',
        'gender_id', 'relationship_id', 'civil_status_id',
        'education_level_id', 'is_pwd', 'is_pregnant', 'is_senior',
    ];

    protected $casts = [
        'birth_date'         => 'date',
        'is_pwd'             => 'boolean',
        'is_senior'          => 'boolean',
        'is_pregnant'        => 'boolean',
        'is_graduate'        => 'boolean',
        'age'                => 'integer',
        'gender_id'          => 'integer',
        'relationship_id'    => 'integer',
        'civil_status_id'    => 'integer',
        'education_level_id' => 'integer',
    ];

    protected $appends = ['full_name', 'vulnerability'];

    // ── Model lifecycle hooks ─────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Member $member) {
            $member->member_id ??= (string) Str::uuid();
            if (!empty($member->birth_date)) {
                $age = \Carbon\Carbon::parse($member->birth_date)->age;
                $member->attributes['age'] ??= $age;
                $member->attributes['is_senior'] ??= ($age >= 60 ? 1 : 0);
            }
            if (!empty($member->sex) && empty($member->attributes['gender'])) {
                $member->attributes['gender'] = ($member->sex === 'M' || $member->sex === 'm') ? 'Male' : 'Female';
            }
            if ($member->tempIsPwd !== null) {
                $member->attributes['is_pwd'] = $member->tempIsPwd ? 1 : 0;
            }
            if ($member->tempIsPregnant !== null) {
                $member->attributes['is_pregnant'] = $member->tempIsPregnant ? 1 : 0;
            }
            $member->normalizeAttributesForSchema();
        });

        static::updating(function (Member $member) {
            if (!empty($member->birth_date)) {
                $age = \Carbon\Carbon::parse($member->birth_date)->age;
                $member->attributes['age'] = $age;
                $member->attributes['is_senior'] = ($age >= 60 ? 1 : 0);
            }
            if (!empty($member->sex)) {
                $member->attributes['gender'] = ($member->sex === 'M' || $member->sex === 'm') ? 'Male' : 'Female';
            }
            if ($member->tempIsPwd !== null) {
                $member->attributes['is_pwd'] = $member->tempIsPwd ? 1 : 0;
            }
            if ($member->tempIsPregnant !== null) {
                $member->attributes['is_pregnant'] = $member->tempIsPregnant ? 1 : 0;
            }
            $member->normalizeAttributesForSchema();
        });

        static::saved(function (Member $member) {
            $member->syncVulnerabilityGroup('pwd', $member->tempIsPwd);
            $member->syncVulnerabilityGroup('pregnant', $member->tempIsPregnant);
        });

        static::deleting(function (Member $member) {
            $occId = $member->attributes['occupation'] ?? null;
            if ($occId && is_numeric($occId)) {
                try {
                    DB::table('occupations')
                        ->where('occuaption_id', $occId)
                        ->where('occupation_name', 'Teacher')
                        ->delete();
                } catch (\Throwable) {}
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function household()
    {
        return $this->belongsTo(Household::class, 'household_id', 'household_id');
    }

    // ── Custom query builder ──────────────────────────────────────────────────

    public function newEloquentBuilder($query): MemberQueryBuilder
    {
        return new MemberQueryBuilder($query);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function normalizeAttributesForSchema(): void
    {
        try {
            $tableColumns = array_flip(Schema::getColumnListing($this->getTable()));
            $this->attributes = array_intersect_key($this->attributes, $tableColumns);
        } catch (\Throwable) {
            // Ignore schema lookups for legacy / partially migrated databases.
        }
    }

    public static function sanitizeInsertData(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            if (!is_string($key) && !is_int($key)) {
                continue;
            }

            $clean[$key] = $value;
        }

        try {
            $tableColumns = array_flip(Schema::getColumnListing((new static)->getTable()));
            return array_intersect_key($clean, $tableColumns);
        } catch (\Throwable) {
            return $clean;
        }
    }

    private function syncVulnerabilityGroup(string $key, ?bool $flag): void
    {
        if ($flag === null) {
            return;
        }

        $group = DB::table('vulnerable_groups')->where('vulnerable_group_key', $key)->first();
        if (!$group) {
            return;
        }

        if ($flag) {
            DB::table('member_vulnerable_groups')->insertOrIgnore([
                'member_id'          => $this->member_id,
                'vulnerable_group_id' => $group->vulnerable_group_id,
            ]);
        } else {
            DB::table('member_vulnerable_groups')
                ->where('member_id', $this->member_id)
                ->where('vulnerable_group_id', $group->vulnerable_group_id)
                ->delete();
        }
    }
}
