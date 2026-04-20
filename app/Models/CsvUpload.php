<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CsvUpload extends Model
{
    protected $fillable = [
        'data_source_id',
        'file_name',
        'total_records',
        'successful_records',
        'failed_records',
    ];

    public function dataSource()
    {
        return $this->belongsTo(DataSource::class);
    }
}
