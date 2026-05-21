<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use HasFactory;

    protected $table='tbl_challenge';


    function transalations() {
        return $this->hasMany('App\Models\ChallengeTranslations', 'id_challenge', 'id');
    }

}
