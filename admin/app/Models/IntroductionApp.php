<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntroductionApp extends Model
{
    use HasFactory;
    protected $table = 'tbl_introduction_app';

    //baảng dịch dữ liệu sản phẩm
    function transalations() {
        return $this->hasMany('App\Models\IntroductionAppTranslations', 'id_introduction_app', 'id');
    }

}
