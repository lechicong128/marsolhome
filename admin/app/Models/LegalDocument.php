<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    use HasFactory;
    protected $table = 'tbl_legal_documents';

    public function home()
    {
        return $this->hasMany('App\Models\Home', 'legal_id', 'id');
    }
    
}
