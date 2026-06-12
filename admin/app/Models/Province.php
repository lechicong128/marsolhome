<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $table = 'tbl_provinces';
    protected $primaryKey = 'id';

    public function homes()
    {
        return $this->hasMany('App\Models\Home', 'province_id', 'id');
    }

}
