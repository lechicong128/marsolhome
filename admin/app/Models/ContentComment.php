<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentComment extends Model
{
    use HasFactory;
    protected $table = 'tbl_content_comment';
    
    protected $fillable = [
        'content'
    ];
}
