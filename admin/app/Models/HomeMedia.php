<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeMedia extends Model
{
    use HasFactory;

    protected $table = 'tbl_home_media';

    protected $fillable = ['home_id', 'url', 'caption', 'sort_order'];

    public function home()
    {
        return $this->belongsTo(Home::class, 'home_id');
    }
}
