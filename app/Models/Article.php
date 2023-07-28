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
        'contents',
        'seo_title',
        'seo_description',
        'status',
        'upload_id',
        'user_id',
    ];

    public function users(){
        return $this->belongsTo(User::class);
    }

    public function categories(){
        return $this->belongsToMany(Category::class, 'article_category', 'article_id', 'category_id');
    }
    public function articleDetails(){
        return $this->hasMany(ArticleDetail::class);
    }

}
