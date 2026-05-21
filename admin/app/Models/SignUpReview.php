<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignUpReview extends Model
{
    use HasFactory;
    protected $table = 'tbl_sign_up_review';

    function clients_review() {
        return $this->hasMany('App\Models\ClientsReview', 'id_review', 'id')
            ->where('type_object', 'sign_up');
    }

//    function clients_review_sign_up() {
//        return $this->hasMany('App\Models\ClientsReview', 'id_review', 'id')
//            ->where('type_object', 'sign_up');
//    }
//
//    function clients_review_transaction() {
//        return $this->hasMany('App\Models\ClientsReview', 'id_review', 'id')
//            ->where('type_object', 'transaction');
//    }
}
