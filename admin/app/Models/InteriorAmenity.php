<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InteriorAmenity extends Model
{
    use HasFactory;
    protected $table = 'tbl_interior_amenities';

    public function homes()
    {
        return $this->belongsToMany('App\Models\Home', 'tbl_interior_amenities_home', 'interior_amenity_id', 'home_id');
    }
}
