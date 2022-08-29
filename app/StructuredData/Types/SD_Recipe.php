<?php

namespace App\StructuredData\Types;

use App\Article\Factory\StructuredDataFactory;
use App\StructuredData\Concreats\HasSection;
use App\StructuredData\Concreats\HasStep;
use App\StructuredData\Concreats\HasVideo;
use App\StructuredData\StructuredData;
use Symfony\Component\DomCrawler\Crawler;

class SD_Recipe extends StructuredData implements HasStep, HasVideo, HasSection {

    public string $name = '';
    public string $datePublished = '';
    public string $dateModified = '';
    public string $description = '';
    public string $recipeCategory = '';
    public array $recipeIngredient = [];
    public array $recipeInstructions = [];
    public ?SD_ImageObject $image = null;
    public ?SD_aggregateRating $aggregateRating = null;

    /// instructions from dom
    public array $domInstructions = [];

    private int $totalVideos = 0;

    public function process() {
        $this->setPropertyValuesByName(['name', 'datePublished', 'dateModified', 'description',
            'recipeIngredient', 'recipeCategory',
            ['type' => 'SD_TYPE', 'name' => 'image'],
            ['type' => 'SD_TYPE', 'name' => 'aggregateRating']
        ]);

        $this->extractRecipeInstructions();

        $this->extractFromDOM();
    }

    private function extractRecipeInstructions() {
        $instructions = $this->structuredData->recipeInstructions ?? [];

        foreach ($instructions as $instruct) {
            $SDInstruct  = StructuredDataFactory::make($instruct, $this->articleDetail, $this);
            $this->recipeInstructions[] = $SDInstruct;

            $this->totalVideos += $SDInstruct->hasVideo() ? 1 : 0;

        }
    }

    public function getSteps() : array {
        return $this->recipeInstructions;
    }

    public function getTotalVideos() : int {
        return $this->totalVideos;
    }

    private function extractFromDOM() {
        $nodes = $this->articleDetail->getDomCrawler()->filter(".steps .in-block .mw-headline");
        $items = [];
        /**
         * @param Crawler $node
         */
        if($nodes->count()){ // each section
            $nodes->each(function ($node) use(&$items){
                $sectionText    = $node->text();
                $sectionSteps = $node->closest('.section')->filter('.step'); // get all steps of section
                $steps = [];
                if($sectionSteps->count()){ // get step data
                    $sectionSteps->each(function ($step) use(&$steps){

                        $image = $this->getStepImage($step);

                        $stepData = (object)[
                            '@type' => 'HowToStep',
                            'text' => $step->text(),
                            'image' => $image
                        ];
                        $steps[] = $stepData;
                    });
                }

                $sectionData = [
                    'name' => $sectionText,
                    'itemListElement' => $steps
                ];

                $section = StructuredDataFactory::makeByName('HowToSection', $sectionData, $this->articleDetail, $this);
                $this->domInstructions[] = $section;
            });
        }

    }

    //get image of each step
    private function getStepImage($step): string {

        // get from img tag
        $image = $step->siblings()?->filter('.largeimage video');
        if ($image->count()) {
            $image = $image->attr('data-poster');
        }
        else {
            // if not then get from poster of image tag
            $image = $step->siblings()?->filter('.largeimage img');
            if ($image->count()) {
                $image = $image->attr('data-src');
            }
        }

        if(empty($image))
            $a=1;

        return $image;
    }

    public function getSections() : array {
        return $this->domInstructions;
    }
}
