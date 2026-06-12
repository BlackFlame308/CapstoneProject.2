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

    protected static $rowNumColumn = null;

    public static function getRowNumColumn()
    {
        if (self::$rowNumColumn === null) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('import_logs', 'row_num')) {
                    self::$rowNumColumn = 'row_num';
                } else {
                    self::$rowNumColumn = 'row_number';
                }
            } catch (\Throwable $e) {
                self::$rowNumColumn = 'row_number';
            }
        }
        return self::$rowNumColumn;
    }

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
            $col = self::getRowNumColumn();
            if ($col === 'row_num') {
                unset($log->attributes['row_number']);
            } else {
                unset($log->attributes['row_num']);
            }

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
        $col = self::getRowNumColumn();
        return $this->attributes[$col] ?? $this->attributes['row_num'] ?? $this->attributes['row_number'] ?? null;
    }

    public function setRowNumberAttribute($value): void
    {
        $col = self::getRowNumColumn();
        $this->attributes[$col] = $value;
        if ($col !== 'row_num') {
            unset($this->attributes['row_num']);
        }
        if ($col !== 'row_number') {
            unset($this->attributes['row_number']);
        }
    }

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if ($column === 'row_number' || $column === 'row_num') {
                    $column = \App\Models\ImportLog::getRowNumColumn();
                }
                return parent::where($column, $operator, $value, $boolean);
            }

            public function orderBy($column, $direction = 'asc')
            {
                if ($column === 'row_number' || $column === 'row_num') {
                    $column = \App\Models\ImportLog::getRowNumColumn();
                }
                return parent::orderBy($column, $direction);
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
