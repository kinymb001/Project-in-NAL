<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\hasPermission;

class RevisionArticle extends Model
{
    use HasFactory, hasPermission;

    protected $table = 'revision ';
    protected  $fillable = [
        'name',
        'slug',
        'description',
        'contents',
        'seo_title',
        'seo_description',
        'status',
        'upload_id',
        'user_id',
        'revision_number',
        'article_id',
    ];

    public function aticles(){
        return $this->belongsTo(Article::class);
    }

    public function revisionDetail(){
        return $this->hasMany(RevisionDetail::class);
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id')->through('');
    }
}
