<?php

namespace App\StructuredData\Types;

use App\Article\Factory\StructuredDataFactory;
use App\StructuredData\Concreats\HasVideo;
use App\StructuredData\StructuredData;

class SD_HowToSection extends StructuredData implements HasVideo {

    public string $name = '';
    public array $itemListElement = [];
    public array $steps = [];

    private int $totalVideos = 0;

    public function process() {
        $this->setPropertyValuesByName(['name', 'itemListElement']);

        $this->extractSteps();
    }

    private function extractSteps() {
        $steps = $this->itemListElement ?? [];

        foreach ($steps as $step) {
            /** @var SD_HowToStep $howToStep */
            $howToStep     = StructuredDataFactory::make($step, $this->articleDetail, $this);
            $this->steps[] = $howToStep;

            $this->totalVideos += $howToStep->hasVideo() ? 1 : 0;
        }
    }

    public function getTotalVideos() : int {
        return $this->totalVideos;
    }
}
