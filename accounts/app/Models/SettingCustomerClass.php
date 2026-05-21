<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingCustomerClass extends Model
{
    use HasFactory;

    protected $table='tbl_setting_customer_class';

    function rule()
    {
        return $this->hasMany('App\Models\SettingCustomerClassRule', 'setting_customer_class_id', 'id');
    }

    function transalations() {
        return $this->hasMany('App\Models\SettingCustomerClassTranslations', 'setting_customer_class_id', 'id');
    }
}
