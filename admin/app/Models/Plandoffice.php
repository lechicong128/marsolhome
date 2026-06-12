<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plandoffice extends Model
{
    use HasFactory;

    protected $table = 'tbl_plandoffices';

    protected $fillable = [
        'name',
        'province_id',
        'area',
        'kml_file',
        'active'
    ];

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }
}
