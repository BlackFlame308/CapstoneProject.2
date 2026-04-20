<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSource extends Model
{
    protected $fillable = ['type', 'uploaded_by'];

    public function csvUploads()
    {
        return $this->hasMany(CsvUpload::class);
    }

    public function importLogs()
    {
        return $this->hasMany(ImportLog::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
