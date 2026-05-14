<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CsvUpload extends Model
{
    use HasUuids;

    protected $fillable = [
        'data_source_id',
        'file_name',
        'total_records',
        'successful_records',
        'failed_records',
    ];

    protected $casts = [
        'data_source_id'     => 'string',
        'total_records'      => 'integer',
        'successful_records' => 'integer',
        'failed_records'     => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (CsvUpload $upload) {
            $sum = ($upload->successful_records ?? 0) + ($upload->failed_records ?? 0);
            if ($upload->total_records !== null && $sum > $upload->total_records) {
                throw new \LogicException(
                    "CsvUpload integrity error: successful + failed ({$sum}) exceeds total ({$upload->total_records})"
                );
            }
        });
    }

    public function dataSource(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DataSource::class);
    }

    public function importLogs(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            ImportLog::class,
            DataSource::class,
            'id',
            'data_source_id',
            'data_source_id',
            'id'
        );
    }
}
