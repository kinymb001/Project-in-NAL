<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostMetal extends Model
{
    use HasFactory;

    protected $table = 'posts_metal';
    protected $fillable = [
        'post_id',
        'meta_key',
        'meta_value'
    ];

    public function posts()
    {
        return $this->belongsTo(Post::class);
    }
}
