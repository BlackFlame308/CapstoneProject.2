<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CsvUpload extends Model
{
    protected $primaryKey = 'id';
    public $keyType = 'int';
    public $incrementing = false;

    protected $fillable = [
        'data_source_id',
        'file_name',
        'total_records',
        'successful_records',
        'failed_records',
    ];

    protected $casts = [
        'data_source_id'     => 'integer',
        'total_records'      => 'integer',
        'successful_records' => 'integer',
        'failed_records'     => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (CsvUpload $upload) {
            if (empty($upload->id)) {
                try {
                    $upload->id = (\Illuminate\Support\Facades\DB::table('csv_uploads')->max('id') ?? 0) + 1;
                } catch (\Throwable $e) {
                    $upload->id = random_int(100000, 999999);
                }
            }
        });

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
