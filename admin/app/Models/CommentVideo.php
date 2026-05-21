<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentVideo extends Model
{
    use HasFactory;
    protected $table = 'tbl_client_comment_video';

    //baảng dịch dữ liệu sản phẩm
//    function transalations() {
//        return $this->hasMany('App\Models\BannerTranslations', 'id_banner', 'id');
//    }

    function comment_reply()
    {
        return $this->hasMany(CommentVideo::class, 'id_parent', 'id');
    }

    function parent()
    {
        return $this->belongsTo(CommentVideo::class, 'id_parent');
    }

}
