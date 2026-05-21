<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mavinoo\Batch\Traits\HasBatch;

class ScriptDetail extends Model
{
    use HasFactory;
    use HasBatch;
    protected $table = 'tbl_script_detail';

    function transalations() {
        return $this->hasMany('App\Models\ScriptDetailTranslations', 'id_script_detail', 'id');
    }
}

