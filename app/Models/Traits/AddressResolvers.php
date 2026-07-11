<?php

namespace App\Models\Traits;

use App\Models\Traits\NormalizesLocationNames;
use Illuminate\Support\Facades\DB;

/**
 * AddressResolvers Trait
 *
 * Handles all accessor/mutator logic for the Address model,
 * including purok/sitio resolution, zipcode lookup, street
 * normalization, and the full address string builder.
 */
trait AddressResolvers
{
    // ── Street ────────────────────────────────────────────────────────────────

    public function getStreetAttribute(): ?string
    {
        return $this->attributes['street'] ?? $this->attributes['street_address'] ?? null;
    }

    public function setStreetAttribute($value): void
    {
        $this->attributes['street'] = $value;
        if (config('database.default') !== 'sqlite' && is_numeric($value)) {
            $this->attributes['street_address'] = (int) $value;
        }
    }

    // ── House Number ──────────────────────────────────────────────────────────

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

    // ── Purok / Sitio ─────────────────────────────────────────────────────────

    public function getPurokSitioAttribute(): ?string
    {
        if (config('database.default') === 'sqlite' || !empty($this->attributes['purok_sitio'])) {
            return $this->attributes['purok_sitio'] ?? null;
        }

        return $this->resolvePurokSitioFromRelations();
    }

    public function setPurokSitioAttribute($value): void
    {
        $normalized = NormalizesLocationNames::normalizeLocationName($value);
        $this->attributes['purok_sitio'] = $normalized;

        if (config('database.default') === 'sqlite' || empty($normalized)) {
            if (empty($normalized)) {
                $this->attributes['purok_id']  = null;
                $this->attributes['sitio_id']  = null;
            }
            return;
        }

        $barangayId = $this->attributes['barangay_id'] ?? $this->barangay_id ?? null;
        if ($barangayId) {
            $this->attributes['sitio_id'] = $this->findOrCreateSitio($barangayId, $normalized);
        }
    }

    // ── Zip Code ──────────────────────────────────────────────────────────────

    public function getZipCodeAttribute(): ?string
    {
        if (config('database.default') === 'sqlite') {
            return $this->attributes['zip_code'] ?? null;
        }

        if ($this->zipcode_id) {
            $zip = DB::table('zipcodes')->where('zipcode_id', $this->zipcode_id)->first();
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

        $this->attributes['zipcode_id'] = $this->findOrCreateZipcode($value);
    }

    // ── Barangay Name ─────────────────────────────────────────────────────────

    public function getBarangayNameAttribute(): ?string
    {
        if (config('database.default') === 'sqlite') {
            return $this->attributes['barangay_name'] ?? $this->barangay?->name ?? null;
        }
        return $this->barangay?->name ?? null;
    }

    public function setBarangayNameAttribute($value): void
    {
        $this->attributes['barangay_name'] = NormalizesLocationNames::normalizeLocationName($value);
    }

    // ── Full Address ──────────────────────────────────────────────────────────

    public function getFullAddressAttribute(): ?string
    {
        return $this->getFullLocationAttribute();
    }

    public function getFullLocationAttribute(): ?string
    {
        $parts = [];

        if ($this->purok_sitio) {
            $parts[] = $this->purok_sitio;
        }

        $barangayName = $this->barangay
            ? $this->barangay->name
            : ($this->attributes['barangay_name'] ?? null);

        if ($barangayName) {
            $parts[] = $barangayName;
        }

        $city = $this->barangay?->city;
        if ($city)     $parts[] = $city->name;

        $province = $city?->province;
        if ($province) $parts[] = $province->name;

        $region = $province?->region;
        if ($region)   $parts[] = $region->name;

        return implode(' > ', array_reverse($parts)) ?: null;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function resolvePurokSitioFromRelations(): ?string
    {
        $parts = [];

        if ($this->sitio_id) {
            $sitio = DB::table('sitios')->where('sitio_id', $this->sitio_id)->first();
            if ($sitio) $parts[] = $sitio->sitio_name;
        }

        if ($this->purok_id) {
            $purok = DB::table('puroks')->where('purok_id', $this->purok_id)->first();
            if ($purok) $parts[] = $purok->purok_name;
        }

        return count($parts) > 0 ? implode(', ', $parts) : null;
    }

    private function findOrCreateSitio(int $barangayId, string $name): ?int
    {
        $sitio = DB::table('sitios')
            ->where('barangay_id', $barangayId)
            ->where('sitio_name', 'like', $name)
            ->first();

        if ($sitio) {
            return $sitio->sitio_id;
        }

        try {
            return DB::table('sitios')->insertGetId([
                'barangay_id' => $barangayId,
                'sitio_name'  => $name,
            ]);
        } catch (\Throwable) {
            return null;
        }
    }

    private function findOrCreateZipcode(string $zipcode): ?int
    {
        $zip = DB::table('zipcodes')->where('zipcode', $zipcode)->first();
        if ($zip) {
            return $zip->zipcode_id;
        }

        try {
            return DB::table('zipcodes')->insertGetId(['zipcode' => $zipcode]);
        } catch (\Throwable) {
            return null;
        }
    }
}
