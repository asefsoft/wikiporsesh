<?php

namespace App\StructuredData\Types;

use App\Article\Factory\StructuredDataFactory;
use App\StructuredData\Concreats\HasStep;
use App\StructuredData\Concreats\HasVideo;
use App\StructuredData\StructuredData;

class SD_HowTo extends StructuredData implements HasStep, HasVideo {

    public string $name = '';
    public string $datePublished = '';
    public string $dateModified = '';
    public string $description = '';
    public array $sections = [];
    public ?SD_ImageObject $image = null;
    public ?SD_aggregateRating $aggregateRating = null;

    private int $totalVideos = 0;

    public function process() {
        $this->setPropertyValuesByName(['name', 'datePublished', 'dateModified', 'description',
            ['type' => 'SD_TYPE', 'name' => 'image'],
            ['type' => 'SD_TYPE', 'name' => 'aggregateRating']
        ]);

        $this->extractSections();
    }

    private function extractSections() {
        $sections = $this->structuredData->step ?? [];

        foreach ($sections as $section) {
            /** @var SD_HowToSection $section */
            $section          = StructuredDataFactory::make($section, $this->articleDetail, $this);
            $this->sections[] = $section;

            if($section instanceof SD_HowToSection){
                $this->totalVideos += $section->getTotalVideos();
            }
            elseif($section instanceof SD_HowToStep){
                $this->totalVideos += $section->hasVideo() ? 1 : 0;
            }

        }
    }

    public function getSteps() : array {
        $steps = [];

        foreach ($this->sections as $section) {
            // it is not a real section, and it is just a plain HowToStep
            if($section instanceof SD_HowToStep)
                $steps[] = $section;
            elseif ($section instanceof SD_HowToSection)
                $steps = array_merge($steps, $section->steps ?? []);
            else // this should not happen!
                $al=1;

        }

        return $steps;
    }

    public function getTotalVideos() : int {
        return $this->totalVideos;
    }
}
