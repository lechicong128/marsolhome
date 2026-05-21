<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Comment extends Model
{
    protected $table = 'tbl_comments';

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'content'
    ];
 
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
    public function tags()
    {
        return $this->hasMany(CommentTag::class, 'comment_id');
    }
    public function author()
    {
        return $this->belongsTo(Clients::class, 'user_id');
    }
    public function replies()
    {
        $viewerId = request()->client->id ?? 0;

        return $this->hasMany(Comment::class, 'parent_id')
            ->whereNotIn('user_id', function ($q) use ($viewerId) {
                $q->select('blocked_id')->from('tbl_blocked_users')->where('user_id', $viewerId);
            })
            ->whereNotIn('user_id', function ($q) use ($viewerId) {
                $q->select('user_id')->from('tbl_blocked_users')->where('blocked_id', $viewerId);
            });
    }

    public function media()
    {
        return $this->hasMany(CommentMedia::class);
    }
    public function likes()
    {
        return $this->hasMany(CommentLike::class, 'comment_id');
    }
}
