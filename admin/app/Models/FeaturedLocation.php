<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeaturedLocation extends Model
{
    use HasFactory;

    protected $table = 'tbl_featured_locations';
    
    protected $fillable = [
        'province_id',
        'custom_name',
        'image_url',
        'display_order',
        'is_active'
    ];

    public function province()
    {
        return $this->belongsTo('App\Models\Province', 'province_id', 'id');
    }
}
