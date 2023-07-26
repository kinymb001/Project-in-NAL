<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostDetail extends Model
{
    use HasFactory;

    protected $table = 'posts_detail';
    protected $fillable = [
        'name',
        'description',
        'slug',
        'language',
        'post_id'
    ];

    function post()
    {
        return $this->belongsTo(Post::class);
    }
}
