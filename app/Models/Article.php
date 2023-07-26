<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
    protected $table = 'articles';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'content',
        'view',
        'thumbnail',
        'user_id',
        'category_id',
    ];

    public function users(){
        return $this->belongsTo(User::class);
    }

    public function categories(){
        return $this->belongsTo(Category::class);
    }
    public function articleDetails(){
        return $this->hasMany(ArticleDetail::class);
    }
}
