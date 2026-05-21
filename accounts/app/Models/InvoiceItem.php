<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $table='tbl_invoice_item';

    function invoice()
    {
        return $this->belongsTo('App\Models\Invoice', 'invoice_id', 'id');
    }

    function transaction_item()
    {
        return $this->belongsTo('App\Models\TransactionItem', 'transaction_item_id', 'id');
    }
}