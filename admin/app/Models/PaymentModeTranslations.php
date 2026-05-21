<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentModeTranslations extends Model
{
    use HasFactory;
    protected $table = 'tbl_payment_mode_translations';

    public function language_detail()
    {
        return $this->belongsTo('App\Models\Language', 'language', 'code');
    }
}
