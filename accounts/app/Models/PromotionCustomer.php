<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionCustomer extends Model
{
    use HasFactory;

    protected $table='tbl_promotion_customer';

    function promotion()
    {
        return $this->belongsTo('App\Models\Promotion', 'promotion_id', 'id');
    }

    function customer()
    {
        return $this->belongsTo('App\Models\Clients', 'customer_id', 'id');
    }
}
