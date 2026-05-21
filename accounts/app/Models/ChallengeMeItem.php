<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeMeItem extends Model
{
    protected $table = 'challenge_me_items';
    protected $fillable = [
        'challenge_me_id',
        'type',
        'file_url',
        'content',
        'created_by'
    ];

    public function challenge()
    {
        return $this->belongsTo(ChallengeMe::class, 'challenge_me_id');
    }
}