<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentReportComment extends Model
{
    use HasFactory;
    protected $table = 'tbl_content_report_comment';
    
    protected $fillable = [
        'content'
    ];
}
