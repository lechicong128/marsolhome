<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteHaruWallet extends Model
{
    use HasFactory;
    protected $table = 'tbl_note_haru_wallet';

    function transalations() {
        return $this->hasMany('App\Models\NoteHaruWalletTranslations', 'note_haru_wallet_id', 'id');
    }
}
