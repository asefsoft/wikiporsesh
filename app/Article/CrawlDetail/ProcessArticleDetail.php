<?php

namespace App\Article\CrawlDetail;

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

    public function __construct(Url $crawledUrl) {
        $this->crawledUrl = $crawledUrl;
        $this->crawledUrl->load(['article.sections.steps']);
    }

    public function process() {
        $articleUrl = ArticleUrlFactory::make($this->crawledUrl->getFullUrl());

        // get video info
        $this->articleDetail = ArticleDetailFactory::make($articleUrl, $this->crawledUrl);

        $this->articleAlreadyExists = $this->crawledUrl->hasArticle();
        $this->article              = $this->articleAlreadyExists ? $this->crawledUrl->article : null;

        // is crawled article ready to save?
        if ($this->articleDetail->isReadyToBeSaved()) {

            // save article record
            if($this->createOrUpdateArticle($articleUrl)) {
                // persist sections and steps
                $sectionManager = new SectionManager($this->article,
                $this->articleDetail->getArticleSections());
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

    }

    private function createOrUpdateArticle(ArticleUrl $articleUrl) : bool {

        $createFields = [
            'site_id', 'author_id', 'url_id', 'slug',
            'total_sections', 'total_steps', 'title_fa',
            'description_fa', 'description_en', 'image_url',
            'title_en', 'tips_fa', 'tips_en', 'warnings_en',
            'warnings_fa', 'steps_type', 'last_crawled_at'
        ];

        $updateFields = [
            'total_sections',
            'total_steps', 'image_url',
            'title_en',
            'description_en',
            'tips_en',
            'warnings_en',
            'steps_type', 'last_crawled_at'
        ];

        $allData = $this->getFullDetails($articleUrl);

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

        return true;
    }

    private function getFullDetails(ArticleUrl $articleUrl) : array {
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
            'title_fa'       => $title,
            'title_en'       => $title,
            'description_en' => $desc,
            'description_fa' => $desc,
            'tips_fa'        => $tips,
            'tips_en'        => $tips,
            'warnings_en'    => $warning,
            'warnings_fa'    => $warning,
            'steps_type'     => $articleDetail->getStepsType(),
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
                event(new NewArticleCrawled($this));
            else
                event(new ArticleReCrawled($this));
        }
        else
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
