<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Utility extends Model
{
    use HasFactory;

    protected $table = 'tbl_utilities';

    protected $fillable = ['name', 'input_type', 'active', 'icon', 'transaction_type', 'unit'];

    public function options()
    {
        return $this->hasMany(UtilityOption::class, 'utility_id');
    }

    public function type_properties()
    {
        return $this->belongsToMany(TypeProperty::class, 'tbl_type_property_utilities', 'utility_id', 'type_property_id');
    }

    public function homes()
    {
        return $this->belongsToMany(Home::class, 'tbl_home_utilities', 'utility_id', 'home_id')->withPivot('value');
    }
}
