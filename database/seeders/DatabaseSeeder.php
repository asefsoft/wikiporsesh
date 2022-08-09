<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Article;
use App\Models\ArticleSection;
use App\Models\ArticleStep;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->resetArticlesData();

        $articles = Article::factory()->count(30)->make();

        // create articles
        foreach ($articles as $article) {
            $article->save();

            // create sections
            for ($i = 1; $i <= $article->total_sections; $i ++) {
                $section = ArticleSection::factory([
                    'article_id' => $article->id,
                    'order' => $i,
                ])->make();

                $section->save();

                $total_steps = rand(1, 6);

                // create steps
                for ($j = 1; $j <= $total_steps; $j ++) {
                    $step = ArticleStep::factory([
                        'article_id' => $article->id,
                        'section_id' => $section->id,
                        'order' => $j,
                    ])->make();

                    $step->save();
                }
            }

            // attach categories
            $total_categories = rand(1, 3);
            $categories = Category::inRandomOrder()->take($total_categories)->get()->pluck('id');
            $article->categories()->sync($categories);
        }

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }

    private function resetArticlesData() {
        Schema::disableForeignKeyConstraints();
        ArticleStep::truncate();
        ArticleSection::truncate();
        Article::truncate();
        DB::table('article_categories')->truncate();
        Schema::enableForeignKeyConstraints();
    }
}
