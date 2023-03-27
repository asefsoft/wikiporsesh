<?php

namespace App\View\Components;

use App\Models\Article;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class RelatedArticles extends Component
{
    private bool $hasRelatedArticles;
//    public ?Article $article = null;
    public ?Collection $relatedArticles = null;

    public function __construct(public ?Article $article = null)
    { }

    public function hasRelatedArticles() : bool
    {
        $this->checkRelated();

        return $this->relatedArticles->count() > 0;
    }

    public function getRelatedArticles() : Collection
    {
        $this->checkRelated();
        return $this->relatedArticles;
    }

    public function getRelatedSmallDescription(Article $related): string {
        return strLimit($related->description_fa, 110);
    }

    public function getCategoryUrl(): string {
        return route('category-display', ['category' => $this->article->categories->first()->slug]);
    }

    public function getCategoryName(): string {
        return $this->article->categories->first()->name_fa;
    }


    public function render()
    {
        return view('components.related-articles');
    }

    private function checkRelated(): void {
        if (is_null($this->relatedArticles)) {
//            $this->article = $this->attributes->get('article');
            $this->relatedArticles = $this->article->relatedArticles();
//            $this->withAttributes(['relatedArticles' => $this->relatedArticles, 'article' => $this->article]);
        }
    }
}
