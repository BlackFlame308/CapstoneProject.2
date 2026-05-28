<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $primaryKey = 'id';
    public $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'data_source_id',
        'row_number',
        'status',
        'error_message',
    ];

    protected $casts = [
        'data_source_id' => 'integer',
        'row_number'     => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (ImportLog $log) {
            $allowed = ['success', 'failed'];
            if (!in_array($log->status, $allowed, true)) {
                throw new \InvalidArgumentException(
                    "Invalid ImportLog status '{$log->status}'. Allowed: "
                    . implode(', ', $allowed)
                );
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function dataSource(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DataSource::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isFailure(): bool
    {
        return $this->status === 'failed';
    }
}
