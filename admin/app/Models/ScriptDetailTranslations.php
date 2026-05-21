<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mavinoo\Batch\Traits\HasBatch;

class ScriptDetailTranslations extends Model
{
    use HasFactory;
    use HasBatch;
    protected $table = 'tbl_script_detail_translations';


    public function language_detail()
    {
        return $this->belongsTo('App\Models\Language', 'language', 'code');
    }
}

