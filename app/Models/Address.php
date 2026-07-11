<?php

namespace App\Models;

use App\Models\Traits\AddressResolvers;
use App\Models\Traits\NormalizesLocationNames;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Address extends Model
{
    use AddressResolvers;

    protected $primaryKey = 'address_id';
    public    $keyType    = 'int';
    public    $incrementing = false;

    protected $fillable = [
        'street', 'purok_sitio', 'house_number', 'zip_code',
        'full_address', 'barangay_id', 'barangay_name',
        'street_address', 'sitio_id', 'purok_id', 'zipcode_id',
    ];

    protected $casts = [
        'barangay_id'    => 'integer',
        'street_address' => 'integer',
        'sitio_id'       => 'integer',
        'purok_id'       => 'integer',
        'zipcode_id'     => 'integer',
    ];

    // ── Lifecycle hooks ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Address $address) {
            if (empty($address->address_id)) {
                try {
                    $address->address_id = (DB::table('addresses')->max('address_id') ?? 0) + 1;
                } catch (\Throwable) {
                    $address->address_id = random_int(100000, 999999);
                }
            }
        });

        static::saving(function (Address $address) {
            if (config('database.default') === 'sqlite') {
                return;
            }
            if (!empty($address->purok_sitio) && empty($address->sitio_id) && !empty($address->barangay_id)) {
                $normalized       = NormalizesLocationNames::normalizeLocationName($address->purok_sitio);
                $address->purok_sitio = $normalized;
                $address->sitio_id    = $address->findOrCreateSitio($address->barangay_id, $normalized);
            }
        });

        static::deleting(function (Address $address) {
            if (config('database.default') === 'sqlite') {
                return;
            }
            $address->cleanupTestSitio();
            $address->cleanupTestPurok();
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id', 'barangay_id');
    }

    public function household(): HasOne
    {
        return $this->hasOne(Household::class, 'address_id', 'address_id');
    }

    // ── Private cleanup helpers ───────────────────────────────────────────────

    private function cleanupTestSitio(): void
    {
        if ($this->sitio_id) {
            try {
                DB::table('sitios')
                    ->where('sitio_id', $this->sitio_id)
                    ->where('sitio_name', 'like', 'Purok Test%')
                    ->delete();
            } catch (\Throwable) {}
        }
    }

    private function cleanupTestPurok(): void
    {
        if ($this->purok_id) {
            try {
                DB::table('puroks')
                    ->where('purok_id', $this->purok_id)
                    ->where('purok_name', 'like', 'Purok Test%')
                    ->delete();
            } catch (\Throwable) {}
        }
    }
}
