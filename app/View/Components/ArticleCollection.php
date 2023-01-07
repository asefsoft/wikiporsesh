<?php

namespace App\View\Components;

use App\View\ArticleCollectionData;
use Illuminate\View\Component;

class ArticleCollection extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public ArticleCollectionData $articleCollection)
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.article-collection');
    }
}
