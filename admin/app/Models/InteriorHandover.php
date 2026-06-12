<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InteriorHandover extends Model
{
    use HasFactory;
    protected $table = 'tbl_interior_handovers';

    public function home()
    {
        return $this->hasMany('App\Models\Home', 'interior_id', 'id');
    }
}
