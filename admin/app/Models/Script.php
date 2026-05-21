<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mavinoo\Batch\Traits\HasBatch;

class Script extends Model
{
    use HasFactory;
    use HasBatch;
    protected $table = 'tbl_script';
}

