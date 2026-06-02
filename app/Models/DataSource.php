<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSource extends Model
{
    protected $primaryKey = 'id';
    public $keyType = 'int';
    public $incrementing = false;

    protected $fillable = [
        'type',
        'uploaded_by',
    ];

    protected $casts = [
        'uploaded_by' => 'string',
    ];

    protected $with = ['uploader'];

    protected static function booted(): void
    {
        static::creating(function (DataSource $source) {
            if (empty($source->id)) {
                try {
                    $source->id = (\Illuminate\Support\Facades\DB::table('data_sources')->max('id') ?? 0) + 1;
                } catch (\Throwable $e) {
                    $source->id = random_int(100000, 999999);
                }
            }
        });

        static::saving(function (DataSource $source) {
            $allowed = ['csv'];
            if (!in_array($source->type, $allowed, true)) {
                throw new \InvalidArgumentException(
                    "Invalid DataSource type '{$source->type}'. Allowed: " . implode(', ', $allowed)
                );
            }
        });
    }

    public function csvUpload(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CsvUpload::class);
    }

    public function importLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ImportLog::class);
    }

    public function uploader(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'user_id');
    }
}
