<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerClass extends Model
{
    use HasFactory;

    protected $table='tbl_customer_class';

    function setting_customer_class()
    {
        return $this->belongsTo('App\Models\SettingCustomerClass', 'setting_customer_class_id', 'id');
    }

    function customer()
    {
        return $this->hasOne('App\Models\Clients', 'customer_id', 'id');
    }

}
