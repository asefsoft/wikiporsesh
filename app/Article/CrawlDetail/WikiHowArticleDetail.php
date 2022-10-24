<?php


namespace App\Article\CrawlDetail;

use App\StructuredData\PageStructuredData;
use Brick\StructuredData\Item;
use Brick\StructuredData\Reader\JsonLdReader;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class WikiHowArticleDetail extends ArticleDetail
{

    public function parseArticleInfo(): bool {

        $this->extractStepsType();
        $this->extractStepsVideos();
        $this->extractLDJsonScripts();
        $this->processLdJsonScripts();

        return true;
    }

    // methods, parts, sections, steps, IN THIS ARTICLE, or empty
    public function extractStepsType() {
         $node = $this->domCrawler->filter('#method_toc_list .toc_header')->first();
         if($node->count())
            $this->stepsType = $node->text();
    }

    private function extractStepsVideos() {
        $nodes = $this->domCrawler->filter('video');
        $videosData = [];
        $notGoodVideos = [];
        //$node->attr('data-src');
        if($nodes->count()){
            $nodes->each(function ($node) use(&$videosData, &$notGoodVideos) {
                $ancestors   = $node->ancestors();

                $parentClass = $ancestors->eq(0)->attr('class');
                if(str_contains($parentClass, "video-container")) {

                    // find the nearest parent with step-id-* pattern
                    $stepIDItem = $ancestors->closest('[id^="step-id-"]');
                    $stepID = $stepIDItem?->attr('id');
                    $stepText = $stepIDItem?->filter('.step')->text();
                    //remove [8] reference texts
                    $stepText = $this->stripExtraWords($stepText);

                    $videosData[] = [
                        'id' =>  $stepID,
                        'src' => $node->attr('data-src'),
                        'mapped' => 'NO',
                        'stepText' => unescape_unicode(Str::limit($stepText, 80, ''))
                    ];

                    //$data[] = $node->ancestors();
                }
                else {
                    $notGoodVideos[] = $node->attr('data-src');
                }
            });
            $this->stepsVideos = $videosData;
            $this->otherVideos = $notGoodVideos;
        }
    }

    public function processLdJsonScripts() {

        echo '<pre>', $this->sourceUrl , PHP_EOL, PHP_EOL;

        if($this->hasLdJsonData()) {
            $this->pageStructuredData = new PageStructuredData($this->ldJsonData, $this);

            $this->articleSections = $this->pageStructuredData->getAllSections();
            $this->articleSteps = $this->pageStructuredData->getAllSteps();
            $this->articleTitle = $this->pageStructuredData->getArticle()?->name ?? '';
            $this->articleDescription = $this->pageStructuredData->getArticle()?->description ?? '';
            $this->articleImageUrl = $this->pageStructuredData->getArticle()?->image?->url ?? '';

            $this->extractTipsAndWarnings();

            //CategoryManager::addCategoriesToArticle($this->pageStructuredData->getBreadCrumbs(), Article::first());
            echo "Ready To Save: ", $this->pageStructuredData->hasEnoughStepsAndSections() ? "YES" : "NO!", PHP_EOL, PHP_EOL;
            echo implode("\n", $this->pageStructuredData->getBreadCrumbs(true)), PHP_EOL, PHP_EOL;
            echo "Article Type: ", $this->pageStructuredData->getArticleInstructionType(), PHP_EOL;

            echo "Steps DOM Videos: ", count($this->stepsVideos ?? []) , PHP_EOL;
            echo "Steps Mapped Videos: ", $this->pageStructuredData->getTotalVideos() , PHP_EOL;
            echo PHP_EOL, json_encode($this->stepsVideos ?? [], JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES) , PHP_EOL , PHP_EOL;

            echo "Other Videos: ", count($this->otherVideos ?? []) , PHP_EOL;
            echo implode("\n", $this->otherVideos ?? []) , PHP_EOL , PHP_EOL;

            echo "Steps Type: ", $this->getStepsType(), PHP_EOL;
            $steps = collect($this->pageStructuredData->getAllSteps() ?? []);
            $steps = only_fields($steps, ['text', 'videoUrl', 'overallStepNumber']);
            echo implode("\n", $steps->toArray()) , PHP_EOL;
        }

        //\File::put(storage_path("test-ld.json"), json_encode($scripts->first(), JSON_PRETTY_PRINT));
        $reader = new JsonLdReader();
        $items = $reader->read($this->domCrawler->getNode(0)->ownerDocument, $this->sourceUrl);
        //print_r($items);

        foreach ($items as $item) {
            $types = implode(',', $item->getTypes());

            if(str_contains($types, "BreadcrumbList"))
                continue;

            echo PHP_EOL, $types, " <<< " , PHP_EOL;

            foreach ($item->getProperties() as $name => $values) {
                foreach ($values as $value) {
                    if ($value instanceof Item) {
                        // We're only displaying the class name in this example; you would typically
                        // recurse through nested Items to get the information you need
                        $properties = $value->getProperties();
                        $value = '(' . implode(', ', $value->getTypes()) . ")\n";
                        foreach ($properties as $key => $property) {
                            $value .= sprintf("\t%s: %s\n", $key, json_encode($property, JSON_UNESCAPED_SLASHES));
                        }
                    }

                    // If $value is not an Item, then it's a plain string
                    echo "  - $name: $value", PHP_EOL;
                }
            }
        }


    }

    private function extractTipsAndWarnings(){
        $this->articleTips = $this->extractByType('tips');
        $this->articleWarnings = $this->extractByType('warnings');
    }

    private function extractByType($cssId) : string {
        $nodes = $this->domCrawler->filter('#' . $cssId);
        $text = [];

        if ($nodes->count()) { // find li s
            $items = $nodes->filter('li');

            if($items->count()){
                $items->each(function ($liItem) use(&$text){
                    $text[] = "* " . $liItem->filter('div')->text(); // all li s
                });

            }
            else {
                if(!str_starts_with($nodes->text(), "Submit a Tip"))
                    $text[] = $this->stripExtraWords($nodes->text());
            }
        }

        return implode("\n", $text);
    }

    public function getLdJsonData() : ?Collection {
        return $this->ldJsonData;
    }


    public function isReadyToBeSaved() : bool {
        return ($this->pageStructuredData?->hasEnoughStepsAndSections() ?? false) &&
               ($this->pageStructuredData?->hasArticle() ?? false);
    }

    private function stripExtraWords($text) : string|array {
        $text = preg_replace("~\[[0-9]*]~", '', $text, - 1);
        $text = preg_replace("~Thanks! Helpful \d+ Not Helpful \d+~", '', $text, - 1);
        $text = str_replace(" X Research source", '', $text);

        return $text;
    }

}
