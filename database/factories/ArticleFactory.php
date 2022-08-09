<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleSection;
use App\Models\Url;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;


    public function definition() : array {

        $hasTip = rand(1, 100) <= 30;
        $hasWarning = rand(1, 100) <= 10;
        $totalSections = rand(1, 6);
        $stepsType = match (rand(1,3)) {
            1 => 'Part',
            2 => 'Step',
            3 => 'Method',
        };

        $title = fake()->sentence;
        $slug = str($title)->slug()->toString();

        $likes = rand(0, 200);
        $dislikes = rand(0, 50);

        return [
            'site_id' => 1,
            'author_id' => User::inRandomOrder()->first(),
            'url_id' => Url::inRandomOrder()->first(),

            'slug' => $slug,
            'title_en' => $title,
            'title_fa' => fake()->realText,
            'tips_fa' => $hasTip ? str(fake()->realText(300))->limit(300, '') : null,
            'tips_en' => $hasTip ? str(fake()->sentence(60))->limit(300, '') : null,
            'warnings_fa' => $hasWarning ? str(fake()->realText(300))->limit(300, '') : null,
            'warnings_en' => $hasWarning ? str(fake()->sentence(60))->limit(300, '') : null,
            'total_sections' => $totalSections,
            'has_steps' => 1,
            'steps_type' => $stepsType,
            'views' => rand(50,20000),
            'likes' => $likes,
            'dislikes' => $dislikes,
            'rate' => round(($likes/($likes+$dislikes)) * 100, 1),
            'is_featured' => rand(0, 100) < 20
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Article $article) {
//            $this->makeSectionsAndSteps($article);
        })->afterCreating(function (Article $article) {
//            $this->makeSectionsAndSteps($article);
        });
    }

    private function makeSectionsAndSteps(Article $article) {
//        echo "here";
//        dump(23232323);
//        for($i = 1; $i <= $article->total_sections; $i++){
//            $section = ArticleSection::factory(['article_id' => $article->id]);
//        }
    }
}
