<?php

namespace App\Article;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Collection;

class RelatedArticles
{
    // constructor
    public function __construct(private Article $article)
    {
    }

    // get related articles base on categories of article
    public function getArticles(int $limit = 10, int $offset = 0) : Collection
    {
        $categories = Category::getAllCategoriesAndSubCategories($this->article->categories->pluck('id')->toArray());

        $relatedArticles = Article::where('articles.id', '!=', $this->article->id)
            ->join('article_categories', 'articles.id', '=', 'article_categories.article_id')
            ->whereIn('article_categories.category_id', $categories)
            //todo enable this on production
//            ->whereNotNull('published_at')
            ->limit($limit)
            ->offset($offset)
            ->get();

        return $relatedArticles;
    }

}
