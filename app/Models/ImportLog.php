<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $fillable = [
        'data_source_id',
        'row_number',
        'status',
        'error_message',
    ];

    public function dataSource()
    {
        return $this->belongsTo(DataSource::class);
    }
}
