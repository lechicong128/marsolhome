<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientIntroduce extends Model
{
    use HasFactory;

    protected $table='tbl_client_introduce';

    function customer()
    {
        return $this->belongsTo('App\Models\Clients', 'id_client', 'id');
    }

    function parent()
    {
        return $this->belongsTo('App\Models\Clients', 'id_client_introduce', 'id');
    }
}
