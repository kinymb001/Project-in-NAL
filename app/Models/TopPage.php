<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization',
        'area',
        'overview',
        'about',
        'summary',
        'cover_image',
        'profile_image',
        'intro_video',
        'official_website',
        'fb_link',
        'insta_link',
        'status'
    ];

    public function topPageDetail()
    {
        return $this->hasMany(TopPageDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
