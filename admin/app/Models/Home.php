<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Home extends Model
{
    use HasFactory;

    protected $table = 'tbl_home';

    public function interior_amenities()
    {
        return $this->belongsToMany('App\Models\InteriorAmenity', 'tbl_interior_amenities_home', 'home_id', 'interior_amenity_id');
    }

    public function propertyType()
    {
        return $this->belongsTo('App\Models\TypeProperty', 'property_type', 'id');
    }

    public function province()
    {
        return $this->belongsTo('App\Models\Province', 'province_id', 'id');
    }

    public function ward()
    {
        return $this->belongsTo('App\Models\Ward', 'ward_id', 'id');
    }

    public function direction()
    {
        return $this->belongsTo('App\Models\HouseOrientation', 'direction_id', 'id');
    }

    public function legal()
    {
        return $this->belongsTo('App\Models\LegalDocument', 'legal_id', 'id');
    }

    public function interior()
    {
        return $this->belongsTo('App\Models\InteriorHandover', 'interior_id', 'id');
    }

    public function media_items()
    {
        return $this->hasMany(HomeMedia::class, 'home_id')->orderBy('sort_order');
    }

    public function documents_red()
    {
        return $this->hasMany(HomeDocument::class, 'home_id')->where('type', 1);
    }

    public function documents_other()
    {
        return $this->hasMany(HomeDocument::class, 'home_id')->where('type', 2);
    }

    public function utilities()
    {
        return $this->belongsToMany(Utility::class, 'tbl_home_utilities', 'home_id', 'utility_id')->withPivot('value');
    }

    public function favourite()
    {
        return $this->hasMany('App\Models\HomeFavourite', 'home_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(\App\Models\CommentHome::class, 'home_id');
    }

    public function reviews()
    {
        return $this->hasMany(\App\Models\HomeReview::class, 'home_id');
    }
    
    public function save_home()
    {
        return $this->hasMany('App\Models\HomeSave', 'home_id', 'id');
    }

}
