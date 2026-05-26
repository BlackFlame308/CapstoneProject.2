<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VulnerableGroup extends Model
{
    protected $primaryKey = 'vulnerable_group_id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'vulnerable_group_key',
        'vulnerable_group_label',
    ];

    /**
     * Get members in this vulnerable group
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            Member::class,
            'member_vulnerable_groups',
            'vulnerable_group_id',
            'member_id',
            'vulnerable_group_id',
            'member_id'
        );
    }
}
