<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;
    protected $table = 'tbl_banner';

    //baảng dịch dữ liệu sản phẩm
    function transalations() {
        return $this->hasMany('App\Models\BannerTranslations', 'id_banner', 'id');
    }

}
