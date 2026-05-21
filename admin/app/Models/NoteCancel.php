<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteCancel extends Model
{
    use HasFactory;
    protected $table = 'tbl_note_cancel';

    function transalations() {
        return $this->hasMany('App\Models\NoteCancelTranslations', 'note_cancel_id', 'id');
    }
}
