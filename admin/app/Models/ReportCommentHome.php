<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportCommentHome extends Model
{
    use HasFactory;

    protected $table = 'tbl_report_comment_home';

    protected $fillable = [
        'comment_home_id',
        'customer_id',
        'content_report_id',
        'note'
    ];

    public function comment_home()
    {
        return $this->belongsTo(CommentHome::class, 'comment_home_id');
    }

    public function content_report()
    {
        return $this->belongsTo(ContentReportComment::class, 'content_report_id');
    }
}
