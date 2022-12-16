<?php

namespace App\StructuredData;

use App\Article\CrawlDetail\ArticleDetail;
use App\Article\Factory\StructuredDataFactory;
use App\StructuredData\Concreats\HasSection;
use App\StructuredData\Concreats\HasStep;
use App\StructuredData\Concreats\HasVideo;
use App\StructuredData\Types\SD_Article;
use App\StructuredData\Types\SD_BreadcrumbList;
use App\StructuredData\Types\SD_HowTo;
use App\StructuredData\Types\SD_Recipe;
use App\StructuredData\Types\SD_VideoObject;
use App\StructuredData\Types\UnknownStructuredData;
use Illuminate\Support\Collection;

class PageStructuredData {
    protected Collection $structuredDataObject;
    protected Collection $structuredDataProcessed;

    protected int $totalValidItems = 0;
    protected int $totalInvalidItems = 0;

    protected bool $hasArticle = false;
    protected bool $hasBreadcrumb = false;
    protected bool $hasVideoObject = false;
    protected bool $hasHowTo = false;
    protected bool $hasRecipe = false;
    protected array $allSections = [];
    protected array $allSteps = [];
    protected ?SD_Article $article = null;

    private ?ArticleDetail $articleDetail = null;

    public function __construct(Collection $structuredData, ArticleDetail $articleDetail = null) {
        $this->structuredDataObject = $structuredData;
        $this->structuredDataProcessed = collect();
        $this->articleDetail = $articleDetail;

        $this->process();
    }

    public function process() {

        // extract and process all data items
        foreach ($this->structuredDataObject as $structuredDataObject) {

            // some are array of items. like BreadCrumbs
            if(is_array($structuredDataObject)) {
                foreach ($structuredDataObject as $object) {
                    $dataObjectProcessed = StructuredDataFactory::make($object, $this->articleDetail);
                    $this->addProcessedDataObject($dataObjectProcessed);
                }
            }
            else {
                $dataObjectProcessed = StructuredDataFactory::make($structuredDataObject, $this->articleDetail);
                $this->addProcessedDataObject($dataObjectProcessed);
            }
        }

        $this->finalProcess();
    }

    private function addProcessedDataObject($dataObjectProcessed) {
        // add valid items to list
        if(! empty($dataObjectProcessed) && ! $dataObjectProcessed instanceof UnknownStructuredData) {
            $this->structuredDataProcessed->add($dataObjectProcessed);
            $this->checkSDType($dataObjectProcessed);
            $this->totalValidItems++;
        }
        else
            $this->totalInvalidItems++;
    }

    // process counts like total sections and total steps
    private function finalProcess() {
        $this->allSections = $this->getAllSections();
        $this->allSteps = $this->getAllSteps();
    }

    public function getBreadCrumbs($asText = false) : array {
        $list = [];
        foreach ($this->structuredDataProcessed as $dataObjectProcessed) {
            if($dataObjectProcessed instanceof SD_BreadcrumbList)
                if($asText)
                    $list[] = $dataObjectProcessed->getReadableText();
                else
                    $list[] = $dataObjectProcessed->getItems();
        }
        return $list;
    }

    public function getAllSections() : array {
        $list = [];
        foreach ($this->structuredDataProcessed as $dataObjectProcessed) {
            if($dataObjectProcessed instanceof HasSection) {
                $list = array_merge($list, $dataObjectProcessed->getSections());
            }

        }
        return $list;
    }

    public function getAllSteps() : array {
        $list = [];
        foreach ($this->structuredDataProcessed as $dataObjectProcessed) {
            if($dataObjectProcessed instanceof HasStep) {
                $list = array_merge($list, $dataObjectProcessed->getSteps());
            }

        }
        return $list;
    }


    public function getTotalVideos() : int {
        $total = 0;
        foreach ($this->structuredDataProcessed as $dataObjectProcessed) {
            if($dataObjectProcessed instanceof HasVideo) {
                $total += $dataObjectProcessed->getTotalVideos();
            }

        }
        return $total;
    }

    // which structured-data are available?
    private function checkSDType(StructuredData $dataObjectProcessed) {
        if($dataObjectProcessed     instanceof SD_BreadcrumbList)
            $this->hasBreadcrumb = true;
        elseif($dataObjectProcessed instanceof SD_Article) {
            $this->hasArticle = true;
            $this->article = $dataObjectProcessed;
        }
        elseif($dataObjectProcessed instanceof SD_VideoObject)
            $this->hasVideoObject = true;
        elseif($dataObjectProcessed instanceof SD_HowTo)
            $this->hasHowTo = true;
        elseif($dataObjectProcessed instanceof SD_Recipe)
            $this->hasRecipe = true;
    }

    public function getArticle() : ?SD_Article {
        return $this->article;

    }

    // is HowToSteps or Recipe Steps or ...?
    public function getArticleInstructionType() : string {
        $types = [];

        if($this->hasHowTo)
            $types[] = 'HowTo';
        if($this->hasRecipe)
            $types[] = 'Recipe';

        if(count($types) == 0)
            $types[] = 'Unknown Type';

        return implode(", ", $types);
    }

    public function hasArticle() : bool {
        return $this->hasArticle;
    }

    public function hasBreadcrumb() : bool {
        return $this->hasBreadcrumb;
    }

    public function hasVideoObject() : bool {
        return $this->hasVideoObject;
    }

    public function hasHowTo() : bool {
        return $this->hasHowTo;
    }

    public function hasRecipe() : bool {
        return $this->hasRecipe;
    }

    public function getTotalValidItems() : int {
        return $this->totalValidItems;
    }

    public function getTotalInvalidItems() : int {
        return $this->totalInvalidItems;
    }

    public function hasEnoughStepsAndSections() : bool {
        return count($this->allSteps) > 0 && count($this->allSections) > 0;
    }



}
