<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientActionVideo extends Model
{
    use HasFactory;
    protected $table = 'tbl_client_action_video';

//    function video_trailer() {
//        return $this->belongsTo('App\Models\VideoFile', 'id', 'rel_id')
//            ->where('rel_type', 'elearning')->where('is_premium', '0');
//    }
//
//    function list_videos() {
//        return $this->hasMany('App\Models\VideoFile', 'rel_id', 'id')
//            ->where('rel_type', 'elearning')->where('is_premium', '1')
//            ->orderBy('order_premium', 'asc')->orderBy('id', 'asc');
//    }
//
//
//    function unlock() {
//        return $this->hasMany('App\Models\ElearningUnlock', 'id_elearning', 'id');
//    }

}
