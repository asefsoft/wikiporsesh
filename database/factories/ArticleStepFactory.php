<?php

namespace Database\Factories;

use App\Models\ArticleStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ArticleStep>
 */
class ArticleStepFactory extends Factory
{

    protected $model = ArticleStep::class;

    public function definition()
    {
        return [
            'title_en' => fake()->sentence,
            'title_fa' => fake()->realText,

            'content_en' => fake()->sentence(18),
            'content_fa' => fake()->realText(600),

            'image_url' => fake()->imageUrl()
        ];
    }
}
