<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $primaryKey = 'id';
    public $keyType = 'int';
    public $incrementing = false;

    protected $fillable = [
        'data_source_id',
        'row_number',
        'row_num',
        'status',
        'error_message',
    ];

    protected $casts = [
        'data_source_id' => 'integer',
        'row_number'     => 'integer',
        'row_num'        => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (ImportLog $log) {
            if (empty($log->id)) {
                try {
                    $log->id = (\Illuminate\Support\Facades\DB::table('import_logs')->max('id') ?? 0) + 1;
                } catch (\Throwable $e) {
                    $log->id = random_int(100000, 999999);
                }
            }
        });

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

    public function getRowNumberAttribute(): ?int
    {
        return $this->attributes['row_num'] ?? null;
    }

    public function setRowNumberAttribute($value): void
    {
        $this->attributes['row_num'] = $value;
    }

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if ($column === 'row_number') {
                    $column = 'row_num';
                }
                return parent::where($column, $operator, $value, $boolean);
            }
        };
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
