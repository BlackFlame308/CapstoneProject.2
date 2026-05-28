<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Address extends Model
{
    protected $primaryKey = 'address_id';
    public $keyType = 'int';
    public $incrementing = true;

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
        'barangay_id' => 'integer',
    ];

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id', 'barangay_id');
    }

    public function household(): HasOne
    {
        return $this->hasOne(Household::class, 'address_id', 'address_id');
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

        if ($this->barangay || ($this->attributes['barangay_name'] ?? null)) {
            $parts[] = $this->barangay ? $this->barangay->name : ($this->attributes['barangay_name'] ?? null);
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
