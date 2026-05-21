<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagProductTranslations extends Model
{
    use HasFactory;
    protected $table = 'tbl_tag_product_translations';

    public function language_detail()
    {
        return $this->belongsTo('App\Models\Language', 'language', 'code');
    }
}
