<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteAffiliateTranslations extends Model
{
    use HasFactory;
    protected $table = 'tbl_note_affiliate_translations';

    public function language_detail()
    {
        return $this->belongsTo('App\Models\Language', 'language', 'code');
    }
}
