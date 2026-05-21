<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlideIntroduceApp extends Model
{
    use HasFactory;
    protected $table = 'tbl_slide_introduce_app';

    function transalations() {
        return $this->hasMany('App\Models\SlideIntroduceAppTranslations', 'id_slide_introduce_app', 'id');
    }
}
