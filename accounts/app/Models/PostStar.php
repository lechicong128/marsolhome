<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostStar extends Model
{
    protected $table = 'tbl_post_stars';
    public function receipt()
    {
        return $this->belongsTo(Receipt::class, 'id');
    }
    public function user()
    {
        return $this->belongsTo(Clients::class, 'user_id', 'id');
    }
}
