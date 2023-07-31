<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopPageDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization',
        'area',
        'overview',
        'about',
        'summary',
        'language',
        'top_page_id'
    ];

    public function topPage()
    {
        return $this->belongsTo(TopPage::class);
    }
}
