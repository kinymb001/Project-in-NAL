<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMetal extends Model
{
    use HasFactory;

    protected $table = 'users_metal';
    protected $fillable = [
        'user_id',
        'key',
        'value'
    ];

    public function users(){
        return $this->belongsTo(User::class);
    }
}
