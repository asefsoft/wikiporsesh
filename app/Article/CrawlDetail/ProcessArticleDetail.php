<?php

namespace App\Article\CrawlDetail;

use App\Article\Factory\ArticleDetailFactory;
use App\Article\Factory\ArticleUrlFactory;
use App\Models\Url;

class ProcessArticleDetail {

    protected Url $crawledUrl;

    public function __construct(Url $crawledUrl) {
        $this->crawledUrl = $crawledUrl;
    }

    public function process() {
        $urlValidator = ArticleUrlFactory::make($this->crawledUrl->getFullUrl());

        // get video info
        $articleDetail = ArticleDetailFactory::make($urlValidator, $this->crawledUrl);
    }

}
