<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $table='tbl_transaction_item';

    function transaction()
    {
        return $this->belongsTo('App\Models\Transaction', 'transaction_id', 'id');
    }

    function invoice()
    {
        return $this->hasOne('App\Models\InvoiceItem', 'transaction_item_id', 'id');
    }

}
