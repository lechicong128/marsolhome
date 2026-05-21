<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;
    protected $table = 'tbl_unit';


    function product()
    {
        return $this->hasMany('App\Models\Products', 'unit_id', 'id');
    }
}
