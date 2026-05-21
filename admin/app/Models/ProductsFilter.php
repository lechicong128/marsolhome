<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsFilter extends Model
{
    use HasFactory;
    protected $table = 'tbl_products_filter';

    //baảng dịch dữ liệu sản phẩm
    function transalations() {
        return $this->hasMany('App\Models\ProductsFilterTranslations', 'id_product_filter', 'id');
    }

}
