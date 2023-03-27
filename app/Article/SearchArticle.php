<?php

namespace App\Article;

use App\Models\Article;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class SearchArticle
{

    public function __construct(private string $query)
    {
        $this->setQuery($query);
    }

    public function search($perPage = 10) : Paginator
    {
        $builder = Article::with('categories');

        foreach (explode(" ", $this->query) as $word) {
            $builder->orWhere('title_fa', 'like', "%{$word}%")
                ->orWhere('description_fa', 'like', "%{$word}%");
        }

        $articles = $builder->simplePaginate($perPage);

        return $articles;
    }

    public function fullTextSearch($perPage = 10) : Paginator {
        //WITH QUERY EXPANSION
        $articles = Article::with('categories')
            ->whereRaw("MATCH(title_fa, description_fa) AGAINST(? IN NATURAL LANGUAGE MODE)", [$this->query])
            ->simplePaginate($perPage);

        return $articles;

    }

    public function setQuery(string $query): void {
        $query = str_replace("  "," ", trim($query));
        $query = str_replace("%", "", $query);
        $query = str_replace("*", " ", $query);
        $this->query = $query;
    }

}
