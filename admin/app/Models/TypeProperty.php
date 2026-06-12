<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeProperty extends Model
{
    use HasFactory;
    protected $table = 'tbl_type_property';

    public function homes()
    {
        return $this->hasMany('App\Models\Home', 'type_property_id', 'id');
    }

    public function utilities()
    {
        return $this->belongsToMany(Utility::class, 'tbl_type_property_utilities', 'type_property_id', 'utility_id');
    }
}
