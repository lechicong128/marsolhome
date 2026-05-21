<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;
    protected $table = 'tbl_product_category';

    public function category_detail()
    {
        return $this->belongsTo('App\Models\CategoryProducts', 'id_category', 'id');
    }
}
