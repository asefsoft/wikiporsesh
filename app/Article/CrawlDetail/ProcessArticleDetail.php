<?php

namespace App\Article\CrawlDetail;

use App\Article\Category\CategoryManager;
use App\Article\Factory\ArticleDetailFactory;
use App\Article\Factory\ArticleUrlFactory;
use App\Article\Persist\SectionManager;
use App\Article\Url\ArticleUrl;
use App\Events\ArticleReCrawled;
use App\Events\FailedArticleCrawl;
use App\Events\NewArticleCrawled;
use App\Models\Article;
use App\Models\Url;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProcessArticleDetail {

    protected Url $crawledUrl;
    protected ?Article $article = null;
    protected bool $articleAlreadyExists = false;
    protected bool $isPersisted = false;
    protected string $errorMessage = '';
    protected ?ArticleDetail $articleDetail;
    private bool $forceToProcess;
    private bool $ignored = false;

    public function __construct(Url $crawledUrl, bool $forceToProcess = false) {
        $this->crawledUrl = $crawledUrl;
        $this->crawledUrl->load(['article.sections.steps']);
        $this->forceToProcess = $forceToProcess;
    }

    public function process() {

        if(! $this->shouldProcessAndSaveArticle()) {
            $this->ignored = true;
            return;
        }

        $articleUrl = ArticleUrlFactory::make($this->crawledUrl->getFullUrl());

        // get Article info
        $this->articleDetail = ArticleDetailFactory::make($articleUrl, $this->crawledUrl);

        $this->articleAlreadyExists = $this->crawledUrl->hasArticle();
        $this->article              = $this->articleAlreadyExists ? $this->crawledUrl->article : null;

        // is crawled article ready to save?
        if ($this->articleDetail->isReadyToBeSaved()) {

            // save article record
            if($this->createOrUpdateArticle($articleUrl)) {
                // persist sections and steps
                $sectionManager = new SectionManager($this->article, $this->articleDetail->getArticleSections());
                $sectionManager->persist();
                $this->isPersisted  = $sectionManager->isPersisted();
                $this->errorMessage = $sectionManager->getErrorMessage();
            }

        }
        else {
            $this->errorMessage = "Article Details is not ready to be saved!";
        }

        // events
        $this->dispatchEvents();

        unset($articleUrl, $this->articleDetail, $this->article);

    }

    // is crawled url already has an article on our db?
    public function alreadyHasArticle(): bool {
        return $this->crawledUrl->article instanceof Article;
    }

    protected function shouldProcessAndSaveArticle(): bool {
        $should = true;

        // is processed before and has article
        if($this->alreadyHasArticle()) {
            // dont process if it's not forced
            if(! $this->forceToProcess)
                $should = false;
            // dont process if Article was edited by me, even in force mode
            elseif (!empty($this->crawledUrl?->article?->edited_at))
                $should = false;
        }

        return $should;
    }

    private function createOrUpdateArticle(ArticleUrl $articleUrl) : bool {

        $createFields = [
            'site_id', 'author_id', 'url_id', 'slug',
            'total_sections', 'total_steps', 'title_fa',
            'description_fa', 'description_en', 'image_url',
            'title_en', 'tips_fa', 'tips_en', 'warnings_en','is_featured',
            'warnings_fa', 'steps_type', 'last_crawled_at', 'source_views'
        ];

        $updateFields = [
            'total_sections',
            'total_steps', 'image_url',
            'title_en',
            'title_fa',
            'is_featured',
            'description_en',
            'tips_en',
            'warnings_en',
            'source_views',
            'steps_type', 'last_crawled_at'
        ];

        $allData = $this->getArticleFullDetails($articleUrl);

        $requiredFields = $this->articleAlreadyExists ? $updateFields : $createFields;

        $articleData = Arr::only($allData, $requiredFields);

        try {
            if ($this->articleAlreadyExists) {
                $this->article->update($articleData);
            }
            else {
                $this->article = Article::create($articleData);
            }
        } catch (\Exception $e) {
            logException($e, "ProcessArticleDetail:createOrUpdateArticle");
            $this->errorMessage = $e->getMessage();
            return false;
        }

        CategoryManager::addCategoriesToArticle($this->articleDetail->getArticleCategories(), $this->article);


        return true;
    }

    private function getArticleFullDetails(ArticleUrl $articleUrl) : array {
        $articleDetail = $this->articleDetail;
        $title   = Str::limit($articleDetail->getArticleTitle(), 300, '');
        $desc    = Str::limit($articleDetail->getArticleDescription(), 300, '');
        $tips    = Str::limit($articleDetail->getArticleTips(), 500, '');
        $warning = Str::limit($articleDetail->getArticleWarnings(), 500, '');

        return [
            'site_id'        => $articleUrl->getSiteId(),
            'author_id'      => 0, //todo
            'url_id'         => $this->crawledUrl->id,
            'slug'           => Str::limit($articleUrl->getSlug(), 150, ''),
            'image_url'      => Str::limit($articleDetail->getArticleImageUrl(),300 ,''),
            'total_sections' => $articleDetail->getTotalArticleSections(),
            'total_steps'    => $articleDetail->getTotalArticleSteps(),
            'is_featured'    => $articleDetail->isFeaturedArticle(),
            'title_fa'       => $title,
            'title_en'       => $title,
            'description_en' => $desc,
            'description_fa' => $desc,
            'tips_fa'        => $tips,
            'tips_en'        => $tips,
            'warnings_en'    => $warning,
            'warnings_fa'    => $warning,
            'steps_type'     => $articleDetail->getStepsType(),
            'source_views'   => $articleDetail->getArticleViews(),
            'last_crawled_at' => now()
        ];
    }

    public function getErrorMessage() : string {
        return $this->errorMessage;
    }

    public function hasError() : bool {
        return $this->errorMessage != '';
    }

    private function dispatchEvents() {
        if($this->isPersisted){ // is article save successfully
            if($this->articleAlreadyExists)
                event(new ArticleReCrawled($this));
            else
                event(new NewArticleCrawled($this));
        }
        elseif(! $this->ignored)
            event(new FailedArticleCrawl($this)); // there was error?
    }

    public function getArticle() : ?Article {
        return $this->article;
    }

    public function getCrawledUrl() : Url {
        return $this->crawledUrl;
    }

    public function getArticleDetail() : ?ArticleDetail {
        return $this->articleDetail;
    }

}
