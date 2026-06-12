<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $table = 'tbl_blog';

    protected $fillable = [
        'image',
        'title',
        'descption',
        'content',
        'active',
        'type',
        'type_blog',
        'homepage',
        'hot',
    ];

    public function blogCategory()
    {
        return $this->belongsTo(BlogCategory::class, 'type_blog', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'staff_create', 'id');
    }
}
