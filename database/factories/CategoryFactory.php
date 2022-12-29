<?php

namespace Database\Factories;

use App\Article\AssetsManager\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Article\AssetsManager\Category>
 */
class CategoryFactory extends Factory
{

    protected $model = Category::class;

    public function definition()
    {
        $name = fake()->unique()->word();

        return [
            'parent_category_id' => Category::inRandomOrder()->first()->id ?? null,
            'name_en' => $name,
            'name_fa' => $name,
            'slug' => $name,
        ];
    }
}
