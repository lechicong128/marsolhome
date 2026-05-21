<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentTag extends Model
{
    protected $table = 'tbl_comment_tags';

    protected $fillable = [
        'comment_id',
        'user_id',
    ];

    /**
     * Người được tag (user)
     */
    public function user()
    {
        return $this->belongsTo(Clients::class, 'user_id');
    }

    /**
     * Bình luận
     */
    public function comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }
}
