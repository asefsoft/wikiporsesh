<?php

namespace Database\Factories;

use App\Models\ArticleSection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ArticleSection>
 */
class ArticleSectionFactory extends Factory
{

    protected $model = ArticleSection::class;

    public function definition()
    {
        return [
            'title_fa' => fake()->realText,
            'title_en' => fake()->sentence,
            'order' => rand(1,20), //todo: make it sequence
        ];
    }
}
