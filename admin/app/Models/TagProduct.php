<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagProduct extends Model
{
    use HasFactory;
    protected $table = 'tbl_tag_product';

    function transalations() {
        return $this->hasMany('App\Models\TagProductTranslations', 'tag_product_id', 'id');
    }

    function product()
    {
        return $this->belongstoMany('App\Models\Products', 'tbl_tag_product_product', 'tag_product_id', 'product_id');
    }
}
