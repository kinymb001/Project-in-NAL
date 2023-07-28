<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleDetail extends Model
{
    use HasFactory;
    protected $table = 'articles';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'contents',
        'language',
        'article_id',
    ];

    public function articles(){
        return $this->belongsTo(Article::class);
    }

}
