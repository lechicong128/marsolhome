<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Clients extends Model
{
    use HasFactory;
    protected $table='tbl_clients';
    protected $fillable = [
        'fullname',
        'avatar',
        'phone',
        'email',
        'type_client',
        'prefix_phone',
        'password',
        'sign_up_with',
        'id_sign_up',
        'active',
        'gender',
        'birthday',
        'created_at',
        'number_cccd',
        'issued_cccd',
        'date_cccd',
        'number_passport',
        'issued_passport',
        'date_passport',
    ];


    function province()
    {
        return $this->belongstoMany('App\Models\Province', 'tbl_client_address', 'customer_id', 'province_id','id','Id');
    }

    function ward()
    {
        return $this->belongstoMany('App\Models\Ward', 'tbl_client_address', 'customer_id', 'ward_id','id','Id');
    }

    function address()
    {
        return $this->hasMany('App\Models\ClientAddress', 'customer_id', 'id');
    }

    function referral_level()
    {
        return $this->hasOne('App\Models\ReferralLevel', 'customer_id', 'id');
    }

    function referral()
    {
        return $this->hasMany('App\Models\ReferralLevel', 'parent_id', 'id');
    }

    function client_intro_level()
    {
        return $this->hasOne('App\Models\ClientIntroduce', 'id_client', 'id');
    }

    function customer_class()
    {
        return $this->hasOne('App\Models\CustomerClass', 'customer_id', 'id');
    }

    function affiliate()
    {
        return $this->hasMany('App\Models\SyntheticAffiliate', 'customer_id', 'id');
    }

    function referral_lel()
    {
        return $this->hasOne('App\Models\ClientIntroduce', 'id_client', 'id');
    }

    function referral_level_child()
    {
        return $this->hasMany('App\Models\ClientIntroduce', 'id_client_introduce', 'id');
    }

    function client_information_vat()
    {
        return $this->hasOne('App\Models\ClientInformationVat', 'customer_id', 'id');
    }

}
