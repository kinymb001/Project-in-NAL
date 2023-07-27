<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\hasPermission;

class Post extends Model
{
    use HasFactory, SoftDeletes, hasPermission;

    protected $table = 'posts';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'type',
        'upload_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'upload_id' => 'array'
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'categories_posts', 'post_id', 'category_id');
    }

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function post_metas()
    {
        return $this->HasMany(PostMetal::class);
    }

    public function post_detail()
    {
        return $this->hasMany(PostDetail::class);
    }

    public function uploads()
    {
        return $this->hasMany(Upload::class);
    }
}
