<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientsReview extends Model
{
    use HasFactory;
    protected $table = 'tbl_clients_sign_up_review';

    function transalations() {
        return $this->hasMany('App\Models\ProductTranslations', 'id_product', 'id_product' );
    }

    function products() {
        return $this->hasMany('App\Models\Products', 'id', 'id_product');
    }

    function product() {
        return $this->belongsTo('App\Models\Products', 'id_product', 'id');
    }
}
