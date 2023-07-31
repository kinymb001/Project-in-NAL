<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevisionDetail extends Model
{
    use HasFactory;

    protected  $table ='revision_detail';
    protected  $fillable = [
        'name',
        'slug',
        'description',
        'contents',
        'language',
        'seo_title',
        'seo_description',
        'article_id',
        'revision_id',
    ];

    public function revision(){
        return $this->belongsTo(RevisionArticle::class);
    }
}
