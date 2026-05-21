<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMedia extends Model
{
    protected $table = 'tbl_post_media';

    protected $fillable = [
        'post_id',
        'media_url',
        'media_type',
        'sort_order'
    ];
}
