<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $table='tbl_promotion';

    function customer()
    {
        return $this->hasMany('App\Models\PromotionCustomer', 'promotion_id', 'id');
    }

    function transaction()
    {
        return $this->hasMany('App\Models\Transaction', 'promotion_id', 'id');
    }

    function transalations() {
        return $this->hasMany('App\Models\PromotionTranslations', 'promotion_id', 'id');
    }

}
