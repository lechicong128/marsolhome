<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationCommentSuggestion extends Model
{
    use HasFactory;

    protected $table = 'tbl_application_comment';

    protected $fillable = [
        'content'
    ];
}
