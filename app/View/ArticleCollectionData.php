<?php

namespace App\View;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

class ArticleCollectionData
{

    public function __construct(protected $title, protected Paginator $articles) {
    }

    public function getTitle() {
        return $this->title;
    }

    public function getArticles(): Paginator {
        return $this->articles;
    }

    public function getTotalArticle() : int {
        return count($this->articles->items());
    }

    public function shouldShowPaginator() : bool {
        return $this->articles->hasPages();

    }


}
