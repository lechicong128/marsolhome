<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
{
    use HasFactory;

    protected $table = 'tbl_blog_categories';

    protected $fillable = [
        'name',
        'active',
    ];

    public function blogs()
    {
        return $this->hasMany(Blog::class, 'type_blog', 'id');
    }
}
