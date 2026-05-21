<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentPurchase extends Model
{
    use HasFactory;
    protected $table = 'tbl_treatment_purchases';

    public function service()
    {
        return $this->belongsTo(Service::class, 'id_service', 'id');
    }
}
