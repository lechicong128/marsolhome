<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariantOptions extends Model
{
    use HasFactory;
    protected $table = 'tbl_variant_options';



    //baảng dịch dữ liệu sản phẩm
    function transalations() {
        return $this->hasMany('App\Models\VariantOptionsTranslations', 'id_variant_options', 'id');
    }

    function transalations_active() {
        return $this->hasMany('App\Models\VariantOptionsTranslations', 'id_variant_options', 'id')->where('language', app()->getLocale());
    }

    function variant() {
        return $this->belongsTo('App\Models\Variant', 'id_variant', 'id');
    }
//
//    //bảng danh mục sản phẩm
//    function category() {
//        return $this->hasMany('App\Models\ProductCategory', 'id_product', 'id');
//    }
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
