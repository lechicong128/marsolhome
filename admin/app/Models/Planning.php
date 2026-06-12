<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planning extends Model
{
    use HasFactory;

    protected $table = 'tbl_plannings';

    protected $fillable = [
        'name',
        'province_id',
        'location_text',
        'decision_no',
        'scale',
        'status',
        'approved_date',
        'planning_type',
        'image',
        'description',
        'area',
        'kml_file',
        'active'
    ];

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }
}
