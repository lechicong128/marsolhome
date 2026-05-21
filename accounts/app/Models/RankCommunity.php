<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RankCommunity extends Model
{
    use HasFactory;

    protected $table='tbl_rank_community';

//    function rule()
//    {
//        return $this->hasMany('App\Models\SettingCustomerClassRule', 'setting_customer_class_id', 'id');
//    }

    function transalations() {
        return $this->hasMany('App\Models\RankCommunityTranslations', 'id_rank', 'id');
    }
}
