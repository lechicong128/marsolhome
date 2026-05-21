<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventArticles extends Model
{
    use HasFactory;
    protected $table = 'tbl_event_articles';

    function transalations() {
        return $this->hasMany('App\Models\EventArticlesTranslations', 'id_event_articles', 'id');
    }
}
