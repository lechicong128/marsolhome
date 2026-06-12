<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentHome extends Model
{
    use HasFactory;

    protected $table = 'tbl_client_comment_home';

    protected $fillable = [
        'home_id',
        'customer_id',
        'comment',
        'id_parent',
        'reply_to_user_id',
        'count_reply',
        'count_like',
        'count_dislike',
        'data_logs'
    ];

    public function comment_reply()
    {
        return $this->hasMany(CommentHome::class, 'id_parent', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(CommentHome::class, 'id_parent');
    }

    public function home()
    {
        return $this->belongsTo(Home::class, 'home_id');
    }

    public function first_reply()
    {
        return $this->hasOne(CommentHome::class, 'id_parent')
            ->ofMany('id', 'min');
    }
}
