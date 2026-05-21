<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerTranslations extends Model
{
    use HasFactory;
    protected $table = 'tbl_banner_translations';

    public function language_detail()
    {
        return $this->belongsTo('App\Models\Language', 'language', 'code');
    }
}
