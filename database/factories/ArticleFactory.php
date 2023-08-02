<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleDetail;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ArticleFactory extends Factory
{
    const SEO_KEYS = ['Article', 'Development', 'SEO'];
    public function definition()
    {
        $keywords = self::SEO_KEYS;
        $get_title = implode(" - ", $keywords) . " - " . $this->faker->title;
        $get_des = Str::limit($this->faker->paragraph, 150);
        $user_ids = User::inRandomOrder()->pluck('id');
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
            'user_id' =>$this->faker->randomElement($user_ids),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Article $article){
            $languages = config('app.language_array');
            $categoryIds = Category::inRandomOrder()->pluck('id')->first();
            $article->categories()->sync($categoryIds);
            foreach ($languages as $language){
                $article_detail = new ArticleDetail();
                $name =  translate($language, $article->name);
                $article_detail->name = $name;
                $article_detail->slug = Str::of($name)->slug('-');
                $article_detail->contents = translate($language, $article->contents);
                $article_detail->description = translate($language, $article->description);
                $article_detail->article_id = $article->id;
                $article_detail->language = $language;
                $article_detail->save();
            }
        });
    }
}
