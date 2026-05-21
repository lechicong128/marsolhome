<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostReportMode extends Model
{
    protected $table = 'tbl_post_reports';

    protected $fillable = [
        'post_id',
        'user_id',
        'violation_id',
        'note',
        'type',
    ];

    public function violation()
    {
        return $this->belongsTo(ReportViolationMode::class, 'violation_id');
    }

    public function post()
    {
        return $this->belongsTo(ChallengeMeSubmissions::class, 'post_id');
    }

    public function user()
    {
        return $this->belongsTo(Clients::class, 'user_id');
    }
}
