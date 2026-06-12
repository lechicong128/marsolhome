<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HouseOrientation extends Model
{
    use HasFactory;
    protected $table = 'tbl_house_orientations';

    public function homes()
    {
        return $this->hasMany('App\Models\Home', 'direction_id', 'id');
    }
}
