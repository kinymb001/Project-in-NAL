<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Category;

class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user_ids = User::whereHas('roles', function ($query) {
            $query->whereIn('role_id', [1, 2]);
        })->pluck('id');
        $name = $this->faker->name;
        return [
            'user_id' => $this->faker->randomElement($user_ids),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph,
            'status' => $this->faker->randomElement(['public', 'un_public']),
            'type' => $this->faker->title,
            'image_url' => $this->faker->url,
        ];
    }
}
