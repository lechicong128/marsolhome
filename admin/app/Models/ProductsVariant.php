<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsVariant extends Model
{
    use HasFactory;
    protected $table = 'tbl_products_variant';

    public function variant_options()
    {
        return $this->belongsTo('App\Models\VariantOptions', 'id_variant_options', 'id');
    }



//    //baảng dịch dữ liệu sản phẩm
//    function transalations() {
//        return $this->hasMany('App\Models\ProductTranslations', 'id_product', 'id');
//    }
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
