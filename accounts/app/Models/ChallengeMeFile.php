<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeMeFile extends Model
{
    protected $table = 'challenge_me_files';

    protected $fillable = [
        'id',
        'submission_id',
        'challenge_me_id',
        'type',
        'file_url',
        'filename',
        'created_by',
        'created_at',
        'updated_at',
    ];
}
