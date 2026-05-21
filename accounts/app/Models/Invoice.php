<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table='tbl_invoice';


    function invoice_item()
    {
        return $this->hasMany('App\Models\InvoiceItem', 'invoice_id', 'id');
    }

    function customer()
    {
        return $this->belongsTo('App\Models\Clients', 'customer_id', 'id');
    }

}