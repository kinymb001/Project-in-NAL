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
        'type',
    ];

    public function upload(){
        return $this->morphTo();
    }

    public function users(){
        return $this->belongsTo(User::class, 'user_id');
    }


}
