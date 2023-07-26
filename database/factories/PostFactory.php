<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use App\Models\User;
use App\Models\Post;
use App\Models\PostMetal;
use App\Models\PostDetail;
use Illuminate\Support\Str;


class PostFactory extends Factory
{

    public function definition()
    {
        $user_ids = User::whereHas('roles', function ($query) {
            $query->whereIn('role_id', [1, 2]);
        })->pluck('id');
        $name = $this->faker->name;
        return [
            'user_id' =>$this->faker->randomElement($user_ids),
            'name'=>$name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph,
            'status' => $this->faker->randomElement(['public', 'un_public']),
            'type' => $this->faker->title,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Post $post){
            $languages = config('app.language_array');
            $categoryIds = Category::inRandomOrder()->pluck('id')->first();
            $post->categories()->sync($categoryIds);
            foreach ($languages as $language){
                $post_detail = new  PostDetail();
                $post_detail->name = translate($language, $post->name);
                $post_detail->description = translate($language, $post->description);
                $post_detail->slug = Str::slug($post->name);
                $post_detail->language = $language;
                $post_detail->post_id = $post->id;
                $post_detail->save();
            }
            $post_meta = new PostMetal();
            $post_meta->post_id = $post->id;
            $post_meta->meta_key = $this->faker->word;
            $post_meta->meta_value = $this->faker->sentence;
            $post_meta->save();
        });
    }
}
