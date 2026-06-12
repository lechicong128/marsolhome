<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationComment extends Model
{
    use HasFactory;

    protected $table = 'tbl_application_comments';

    protected $fillable = [
        'comment_date',
        'ticket_number',
        'member_id',
        'member_name',
        'content',
        'rating',
        'images',
        'suggestion_ids',
    ];

    protected $casts = [
        'comment_date' => 'datetime',
        'suggestion_ids' => 'array',
    ];
}
