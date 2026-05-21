<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteAffiliate extends Model
{
    use HasFactory;
    protected $table = 'tbl_note_affiliate';

    function transalations() {
        return $this->hasMany('App\Models\NoteAffiliateTranslations', 'note_affiliate_id', 'id');
    }
}
