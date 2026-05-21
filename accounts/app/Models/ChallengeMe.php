<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeMe extends Model
{
    use HasFactory;

    protected $table = 'tbl_challenge_me';

    // function transaction_item()
    // {
    //     return $this->hasMany('App\Models\TransactionItem', 'transaction_id', 'id');
    // }

    function customer()
    {
        return $this->belongsTo('App\Models\Clients', 'client_id', 'id');
    }

    function challenge()
    {
        return $this->belongsTo('App\Models\Challenge', 'id_challenge', 'id');
    }
    // public function media()
    // {
    //     return $this->hasMany(PostMedia::class, 'post_id');
    // }

    public function likes()
    {
        return $this->hasMany(PostLike::class, 'post_id');
    }

    // public function comments()
    // {
    //     return $this->hasMany(Comment::class, 'post_id');
    // }
}
