<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\RevisionArticle;
use App\Models\RevisionDetail;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RevisionArticleFactory extends Factory
{
    const SEO_KEYS = ['Article', 'Development', 'SEO'];
    public function definition()
    {
        $keywords = self::SEO_KEYS;
        $get_title = implode(" - ", $keywords) . " - " . $this->faker->title;
        $get_des = Str::limit($this->faker->paragraph, 150);
        $article_id = Article::inRandomOrder()->pluck('id')->first();
        $user_id = Article::find($article_id)->pluck('user_id');
        $name = $this->faker->name;
        return [
            'name'=>$name,
            'slug' => Str::slug($name),
            'contents' => $this->faker->paragraph,
            'description' => $this->faker->paragraph,
            'seo_title' => $get_title,
            'seo_description' => $get_des,
            'status' => 'pending',
            'upload_id' => null,
            'article_id' => $article_id,
            'user_id' =>$user_id,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (RevisionArticle $revisionArticle){
            $languages = config('app.language_array');
            foreach ($languages as $language){
                $revision_detail = new RevisionDetail();
                $name =  translate($language, $revisionArticle->name);
                $revision_detail->name = $name;
                $revision_detail->slug = Str::of($name)->slug('-');
                $revision_detail->description = translate($language, $revisionArticle->description);
                $revision_detail->article_id = $revisionArticle->id;
                $revision_detail->language = $language;
                $revision_detail->save();
            }
        });
    }
}
