<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;
    protected $table = 'tbl_products';

    function client_review() {
        return $this->belongsTo('App\Models\ClientsReview', 'id', 'id_product');
    }

    function client_reviews() {
        return $this->hasMany('App\Models\ClientsReview', 'id_product', 'id');
    }

    //baảng dịch dữ liệu sản phẩm
    function transalations() {
        return $this->hasMany('App\Models\ProductTranslations', 'id_product', 'id');
    }

    //bảng danh mục sản phẩm
    function category() {
        return $this->hasMany('App\Models\ProductCategory', 'id_product', 'id');
    }

    function tag()
    {
        return $this->belongstoMany('App\Models\TagProduct', 'tbl_tag_product_product', 'product_id', 'tag_product_id');
    }

    function variant_option()
    {
        return $this->belongstoMany('App\Models\VariantOptions', 'tbl_products_variant', 'id_product', 'id_variant_options')->withPivot('price');
    }

    function unit()
    {
        return $this->belongsTo('App\Models\Unit', 'unit_id', 'id');
    }


    //bảng danh mục sản phẩm
//    function reviewer() {
//        return $this->hasMany('App\Models\ProductCategory', 'id_product', 'id');
//    }
}
