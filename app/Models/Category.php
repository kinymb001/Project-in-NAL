<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\hasPermission;

class Category extends Model
{
    use HasFactory, SoftDeletes, hasPermission;

    protected $table = 'categories';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'type',
        'image_url',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'categories_posts', 'category_id', 'post_id');
    }

    public function users(){
        return $this->belongsTo(User::class);
    }

    public function articles(){
        return $this->hasMany(Article::class);
    }

}
