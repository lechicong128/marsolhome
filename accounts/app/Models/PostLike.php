<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostLike extends Model
{
    protected $table = 'tbl_post_likes';

    protected $fillable = [
        'post_id',
        'user_id'
    ];
    public function user()
    {
        return $this->belongsTo(Clients::class, 'user_id', 'id');
    }
}
