<?php

namespace App\Article\CrawlDetail;

use App\Article\Factory\ArticleDetailFactory;
use App\Article\Factory\ArticleUrlFactory;
use App\Article\Url\ArticleUrl;
use App\Models\Article;
use App\Models\Url;

class ProcessArticleDetail {

    protected Url $crawledUrl;
    protected ?Article $article = null;
    protected bool $articleAlreadyExists = false;

    public function __construct(Url $crawledUrl) {
        $this->crawledUrl = $crawledUrl;
    }

    public function process() {
        $articleUrl = ArticleUrlFactory::make($this->crawledUrl->getFullUrl());

        // get video info
        $articleDetail = ArticleDetailFactory::make($articleUrl, $this->crawledUrl);

        $this->articleAlreadyExists = $this->crawledUrl->hasArticle();
        $this->article = $this->articleAlreadyExists ? $this->crawledUrl->article : null;

        if($articleDetail->isReadyToBeSaved())
            $this->createOrUpdateArticle($articleDetail, $articleUrl);

    }

    private function createOrUpdateArticle(ArticleDetail $articleDetail, ArticleUrl $articleUrl) {

        $createFields = [
            'site_id' => $articleUrl->getSiteId(),
            'author_id' => 0,
            'url_id' => $this->crawledUrl->id,
            'slug' => $articleUrl->getSlug(),
            'total_sections' => $articleDetail->getTotalArticleSections(),
            'total_steps' => $articleDetail->getTotalArticleSteps(),
            'title_fa' => $articleDetail->getArticleTitle(),
            'title_en' => $articleDetail->getArticleTitle(),
            'tips_fa' => $articleDetail->getArticleTips(),
            'tips_en' =>  $articleDetail->getArticleTips(),
            'warnings_en' => $articleDetail->getArticleWarnings(),
            'warnings_fa' => $articleDetail->getArticleWarnings(),
            'steps_type' =>  $articleDetail->getStepsType(),
            'last_crawled_at' => now()

        ];

        $updateFields = [
            'total_sections' => $articleDetail->getTotalArticleSections(),
            'total_steps' => $articleDetail->getTotalArticleSteps(),
            'title_en' => $articleDetail->getArticleTitle(),
            'tips_en' =>  $articleDetail->getArticleTips(),
            'warnings_en' => $articleDetail->getArticleWarnings(),
            'steps_type' => $articleDetail->getStepsType(),
            'last_crawled_at' => now()
        ];

    }

}
