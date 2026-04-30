<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Address extends Model
{
    use HasUuids;

    protected $fillable = [
        'street',
        'purok_sitio',
        'house_number',
        'zip_code',
        'full_address',
        'barangay_id',
        'barangay_name',
    ];

    protected $casts = [
        'barangay_id' => 'string',
    ];

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function household(): HasOne
    {
        return $this->hasOne(Household::class);
    }

    /**
     * Convenience accessor for the linked barangay name or manual input.
     */
    public function getBarangayNameAttribute(): ?string
    {
        return $this->barangay?->name ?? $this->attributes['barangay_name'] ?? null;
    }

    /**
     * Get full location path for display.
     */
    public function getFullLocationAttribute(): ?string
    {
        $parts = [];
        
        if ($this->purok_sitio) {
            $parts[] = $this->purok_sitio;
        }

        if ($this->barangay || $this->barangay_name) {
            $parts[] = $this->barangay ? $this->barangay->name : $this->barangay_name;
        }

        $cityObj = $this->barangay?->city;
        if ($cityObj) {
            $parts[] = $cityObj->name;
        }

        $provinceObj = $cityObj?->province;
        if ($provinceObj) {
            $parts[] = $provinceObj->name;
        }

        $regionObj = $provinceObj?->region;
        if ($regionObj) {
            $parts[] = $regionObj->name;
        }

        return implode(' > ', array_reverse($parts));
    }
}

