<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    use HasFactory;
    protected $table = 'tbl_variant';


//
//    //baảng dịch dữ liệu sản phẩm
    function transalations() {
        return $this->hasMany('App\Models\VariantTranslations', 'id_variant', 'id');
    }
    function transalations_active() {
        return $this->hasMany('App\Models\VariantTranslations', 'id_variant', 'id')->where('language', app()->getLocale());
    }
    function variant_options() {
        return $this->hasMany('App\Models\VariantOptions', 'id_variant', 'id');
    }
//
//    function tag()
//    {
//        return $this->belongstoMany('App\Models\TagProduct', 'tbl_tag_product_product', 'product_id', 'tag_product_id');
//    }


    //bảng danh mục sản phẩm
//    function reviewer() {
//        return $this->hasMany('App\Models\ProductCategory', 'id_product', 'id');
//    }
}
