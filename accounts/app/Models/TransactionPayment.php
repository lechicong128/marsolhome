<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionPayment extends Model
{
    use HasFactory;

    protected $table='tbl_transaction_payment';

    function customer()
    {
        return $this->belongsTo('App\Models\Clients', 'customer_id', 'id');
    }

    function transaction()
    {
        return $this->belongsTo('App\Models\Transaction', 'id_transaction', 'id');
    }
}
