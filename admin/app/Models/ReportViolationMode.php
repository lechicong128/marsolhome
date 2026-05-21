<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportViolationMode extends Model
{
    use HasFactory;
    protected $table = 'tbl_reportviolation';
    public function language_detail()
    {
        return $this->belongsTo('App\Models\Language', 'language', 'code');
    }
}
