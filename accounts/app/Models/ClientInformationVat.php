<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientInformationVat extends Model
{
    use HasFactory;

    protected $table='tbl_client_information_vat';


    function customer()
    {
        return $this->hasOne('App\Models\Clients', 'id', 'customer_id');
    }
}
