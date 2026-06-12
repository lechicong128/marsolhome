<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeReview extends Model
{
    use HasFactory;

    protected $table = 'tbl_home_reviews';

    protected $fillable = [
        'home_id',
        'customer_id',
        'poster_id',
        'star',
        'content',
        'status',
    ];

    public function home()
    {
        return $this->belongsTo(Home::class, 'home_id');
    }
}
