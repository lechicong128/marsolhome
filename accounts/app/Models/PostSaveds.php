<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostSaveds extends Model
{
    protected $table = 'tbl_post_saved';
    protected $fillable = ['post_id', 'user_id', 'created_at'];
    public function user()
    {
        return $this->belongsTo(Clients::class, 'user_id', 'id');
    }
}
