<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkShift extends Model
{
    use HasFactory;
    protected $table = 'tbl_work_shifts';
    protected $fillable = ['day_of_week', 'start_time', 'end_time', 'active'];
}
