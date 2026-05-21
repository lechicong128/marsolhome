<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table='tbl_transaction';

    function transaction_item()
    {
        return $this->hasMany('App\Models\TransactionItem', 'transaction_id', 'id');
    }

    function customer()
    {
        return $this->belongsTo('App\Models\Clients', 'customer_id', 'id');
    }

    function customer_leader()
    {
        return $this->belongsTo('App\Models\Clients', 'leader_id', 'id');
    }

    function customer_f1()
    {
        return $this->belongsTo('App\Models\Clients', 'customer_id_f1', 'id');
    }

    function promotion()
    {
        return $this->belongsTo('App\Models\Promotion', 'promotion_id', 'id');
    }

    function payment()
    {
        return $this->belongsTo('App\Models\TransactionPayment', 'id', 'id_transaction');
    }

    function leader()
    {
        return $this->belongsTo('App\Models\Clients', 'leader_id', 'id');
    }

    function invoice_item()
    {
        return $this->belongsTo('App\Models\InvoiceItem', 'transaction_id', 'id');
    }


}
