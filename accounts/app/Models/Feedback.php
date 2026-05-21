<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;
    protected $table = 'tbl_feedback';

//    function customer()
//    {
//        return $this->belongsTo('App\Models\Clients', 'customer_id', 'id');
//    }
}
