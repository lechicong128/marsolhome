<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentMedia extends Model
{
    protected $table = 'tbl_comment_media';

    protected $fillable = [
        'comment_id',
        'media_url',
        'media_type',
    ];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
