<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeFavourite extends Model
{
    use HasFactory;

    protected $table = 'tbl_favourite_home';

    function home()
    {
        return $this->belongsTo('App\Models\Home', 'home_id', 'id');
    }
}
