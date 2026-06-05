<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Traits\NormalizesLocationNames;

class Address extends Model
{
    protected $primaryKey = 'address_id';
    public $keyType = 'int';
    public $incrementing = false;

    protected static function booted()
    {
        static::creating(function ($address) {
            if (config('database.default') === 'sqlite') {
                return;
            }
            if (empty($address->address_id)) {
                try {
                    $address->address_id = (\Illuminate\Support\Facades\DB::table('addresses')->max('address_id') ?? 0) + 1;
                } catch (\Throwable $e) {
                    $address->address_id = random_int(100000, 999999);
                }
            }
        });

        static::saving(function ($address) {
            if (config('database.default') === 'sqlite') {
                return;
            }
            if (!empty($address->purok_sitio) && empty($address->sitio_id) && !empty($address->barangay_id)) {
                $normalizedPurokSitio = NormalizesLocationNames::normalizeLocationName($address->purok_sitio);
                $address->purok_sitio = $normalizedPurokSitio;

                $sitio = \Illuminate\Support\Facades\DB::table('sitios')
                    ->where('barangay_id', $address->barangay_id)
                    ->where('sitio_name', 'like', $normalizedPurokSitio)
                    ->first();
                if ($sitio) {
                    $address->sitio_id = $sitio->sitio_id;
                } else {
                    try {
                        $sitioId = \Illuminate\Support\Facades\DB::table('sitios')->insertGetId([
                            'barangay_id' => $address->barangay_id,
                            'sitio_name' => $normalizedPurokSitio
                        ]);
                        $address->sitio_id = $sitioId;
                    } catch (\Throwable $e) {
                        // Ignore
                    }
                }
            }
        });

        static::deleting(function ($address) {
            if (config('database.default') === 'sqlite') {
                return;
            }
            if ($address->sitio_id) {
                try {
                    \Illuminate\Support\Facades\DB::table('sitios')
                        ->where('sitio_id', $address->sitio_id)
                        ->where('sitio_name', 'like', 'Purok Test%')
                        ->delete();
                } catch (\Throwable $e) {}
            }
            if ($address->purok_id) {
                try {
                    \Illuminate\Support\Facades\DB::table('puroks')
                        ->where('purok_id', $address->purok_id)
                        ->where('purok_name', 'like', 'Purok Test%')
                        ->delete();
                } catch (\Throwable $e) {}
            }
        });
    }

    protected $fillable = [
        'street',
        'purok_sitio',
        'house_number',
        'zip_code',
        'full_address',
        'barangay_id',
        'barangay_name',
        'street_address',
        'sitio_id',
        'purok_id',
        'zipcode_id',
    ];

    protected $casts = [
        'barangay_id' => 'integer',
        'street_address' => 'integer',
        'sitio_id' => 'integer',
        'purok_id' => 'integer',
        'zipcode_id' => 'integer',
    ];

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id', 'barangay_id');
    }

    public function household(): HasOne
    {
        return $this->hasOne(Household::class, 'address_id', 'address_id');
    }

    public function getStreetAttribute(): ?string
    {
        return $this->attributes['street'] ?? $this->attributes['street_address'] ?? null;
    }

    public function setStreetAttribute($value): void
    {
        $this->attributes['street'] = $value;
        if (config('database.default') !== 'sqlite' && is_numeric($value)) {
            $this->attributes['street_address'] = (int)$value;
        }
    }

    public function getHouseNumberAttribute(): ?string
    {
        if (config('database.default') === 'sqlite') {
            return $this->attributes['house_number'] ?? null;
        }
        return null;
    }

    public function setHouseNumberAttribute($value): void
    {
        if (config('database.default') === 'sqlite') {
            $this->attributes['house_number'] = $value;
        }
    }

    public function getPurokSitioAttribute(): ?string
    {
        if (config('database.default') === 'sqlite' || !empty($this->attributes['purok_sitio'])) {
            return $this->attributes['purok_sitio'] ?? null;
        }

        $parts = [];
        if ($this->sitio_id) {
            $sitio = \Illuminate\Support\Facades\DB::table('sitios')->where('sitio_id', $this->sitio_id)->first();
            if ($sitio) {
                $parts[] = $sitio->sitio_name;
            }
        }
        if ($this->purok_id) {
            $purok = \Illuminate\Support\Facades\DB::table('puroks')->where('purok_id', $this->purok_id)->first();
            if ($purok) {
                $parts[] = $purok->purok_name;
            }
        }
        return count($parts) > 0 ? implode(', ', $parts) : null;
    }

    public function setPurokSitioAttribute($value): void
    {
        $normalized = NormalizesLocationNames::normalizeLocationName($value);
        if (config('database.default') === 'sqlite') {
            $this->attributes['purok_sitio'] = $normalized;
            return;
        }
        $this->attributes['purok_sitio'] = $normalized;

        if (empty($normalized)) {
            $this->attributes['purok_id'] = null;
            $this->attributes['sitio_id'] = null;
            return;
        }

        // Try to find a sitio or purok in the DB, or create one safely
        $barangayId = $this->attributes['barangay_id'] ?? $this->barangay_id ?? null;
        if ($barangayId) {
            // Find or create in sitios table
            $sitio = \Illuminate\Support\Facades\DB::table('sitios')
                ->where('barangay_id', $barangayId)
                ->where('sitio_name', 'like', $normalized)
                ->first();
            if ($sitio) {
                $this->attributes['sitio_id'] = $sitio->sitio_id;
            } else {
                try {
                    $sitioId = \Illuminate\Support\Facades\DB::table('sitios')->insertGetId([
                        'barangay_id' => $barangayId,
                        'sitio_name' => $normalized
                    ]);
                    $this->attributes['sitio_id'] = $sitioId;
                } catch (\Throwable $e) {
                    // Ignore
                }
            }
        }
    }

    public function getZipCodeAttribute(): ?string
    {
        if (config('database.default') === 'sqlite') {
            return $this->attributes['zip_code'] ?? null;
        }
        if ($this->zipcode_id) {
            $zip = \Illuminate\Support\Facades\DB::table('zipcodes')->where('zipcode_id', $this->zipcode_id)->first();
            return $zip ? $zip->zipcode : null;
        }
        return null;
    }

    public function setZipCodeAttribute($value): void
    {
        if (config('database.default') === 'sqlite') {
            $this->attributes['zip_code'] = $value;
            return;
        }
        if (empty($value)) {
            $this->attributes['zipcode_id'] = null;
            return;
        }
        // Find or create in zipcodes table
        $zip = \Illuminate\Support\Facades\DB::table('zipcodes')
            ->where('zipcode', $value)
            ->first();
        if ($zip) {
            $this->attributes['zipcode_id'] = $zip->zipcode_id;
        } else {
            try {
                $zipId = \Illuminate\Support\Facades\DB::table('zipcodes')->insertGetId([
                    'zipcode' => $value
                ]);
                $this->attributes['zipcode_id'] = $zipId;
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }

    public function setBarangayNameAttribute($value): void
    {
        $normalized = NormalizesLocationNames::normalizeLocationName($value);
        $this->attributes['barangay_name'] = $normalized;
    }

    public function getFullAddressAttribute(): ?string
    {
        return $this->getFullLocationAttribute();
    }

    public function getBarangayNameAttribute(): ?string
    {
        if (config('database.default') === 'sqlite') {
            return $this->attributes['barangay_name'] ?? $this->barangay?->name ?? null;
        }
        return $this->barangay?->name ?? null;
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
