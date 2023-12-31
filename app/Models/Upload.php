<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    protected $table = 'uploads';
    protected $fillable = [
        'url',
        'thumbnail',
        'user_id',
    ];


    public function users(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function article(){
        return $this->belongsTo(Article::class);
    }
}
