<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostWatcher extends Model
{
    protected $table = 'tbl_post_watchers';
    protected $fillable = ['post_id', 'user_id', 'active'];
}
