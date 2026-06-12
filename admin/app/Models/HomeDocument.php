<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeDocument extends Model
{
    use HasFactory;

    protected $table = 'tbl_home_document';

    protected $fillable = ['home_id', 'url', 'type', 'sort_order'];

    public function home()
    {
        return $this->belongsTo(Home::class, 'home_id');
    }
}
