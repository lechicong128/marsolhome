<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoFile extends Model
{
    use HasFactory;
    protected $table = 'tbl_video_file';

    //baảng dịch dữ liệu sản phẩm
    function products() {
        return $this->belongsTo('app\Models\Products', 'id_product', 'id');
//        return $this->belongsTo(\App\Models\Products::class, 'id_product', 'id');
//        return $this->hasMany('app\Models\Products', 'id', 'id_product');
    }


    function time_play() {
        return $this->hasMany('App\Models\ClientActionVideo', 'id_video', 'id')
            ->where('event', 'play_video');
    }

}
