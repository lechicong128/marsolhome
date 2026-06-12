<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorySearchHome extends Model
{
    use HasFactory;
    protected $table = 'tbl_history_search_home';

    protected $fillable = [
        'id_client',
        'search',
        'id_suggestions'
    ];
}
