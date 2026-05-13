<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceToken extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'household_id',
        'player_id',
        'battery_level',
        'signal_strength',
        'logged_at',
    ];

    protected $casts = [
        'battery_level' => 'integer',
        'signal_strength' => 'integer',
        'logged_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the household this device belongs to
     */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class, 'household_id', 'household_id');
    }

    /**
     * Check if device is currently active (logged in last hour)
     */
    public function isActive(): bool
    {
        return $this->logged_at && $this->logged_at->isAfter(now()->subHour());
    }

    /**
     * Get battery status label
     */
    public function getBatteryStatus(): string
    {
        if (!$this->battery_level) {
            return 'unknown';
        }

        if ($this->battery_level >= 80) {
            return 'full';
        } elseif ($this->battery_level >= 50) {
            return 'good';
        } elseif ($this->battery_level >= 20) {
            return 'low';
        }

        return 'critical';
    }

    /**
     * Get signal strength label
     */
    public function getSignalStatus(): string
    {
        if (!$this->signal_strength) {
            return 'unknown';
        }

        if ($this->signal_strength >= 80) {
            return 'excellent';
        } elseif ($this->signal_strength >= 60) {
            return 'good';
        } elseif ($this->signal_strength >= 40) {
            return 'fair';
        } elseif ($this->signal_strength >= 20) {
            return 'weak';
        }

        return 'very weak';
    }
}
