<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtilityOption extends Model
{
    use HasFactory;

    protected $table = 'tbl_utility_options';

    protected $fillable = ['utility_id', 'name'];

    public function utility()
    {
        return $this->belongsTo(Utility::class, 'utility_id');
    }
}
