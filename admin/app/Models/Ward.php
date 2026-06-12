<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    use HasFactory;
    protected $table = 'tbl_wards_new';
    protected $primaryKey = 'id';

    public function homes()
    {
        return $this->hasMany('App\Models\Home', 'ward_id', 'id');
    }
}
