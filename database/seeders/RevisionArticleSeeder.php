<?php

namespace Database\Seeders;

use App\Models\RevisionArticle;
use Illuminate\Database\Seeder;

class RevisionArticleSeeder extends Seeder
{
    public function run()
    {
        RevisionArticle::factory()->count(50)->create();
    }
}
