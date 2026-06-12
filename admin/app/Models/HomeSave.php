<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeSave extends Model
{
    use HasFactory;

    protected $table = 'tbl_home_save';

    public function home()
    {
        return $this->belongsTo('App\Models\Home', 'home_id', 'id');
    }
}
