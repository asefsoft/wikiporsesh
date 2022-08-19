<?php

namespace App\StructuredData\Types;

use App\StructuredData\StructuredData;
use Illuminate\Support\Str;

class SD_HowToStep extends StructuredData {

    public string $text = '';
    public string $image = '';
    public string $url = '';
    public string $videoUrl = '';
    public bool $hasVideo = false;

    public int $overallStepNumber = 0;
    public int $currentSectionStepNumber = 0;

    public function process() {
        $this->setPropertyValuesByName(['text', 'image', 'url']);
        $this->extractVideoUrl();
        $this->parent?->addStepNumber();
        $this->overallStepNumber = $this->getRootElementOverallStepsCount();
        $this->currentSectionStepNumber = $this->parent->totalCurrentSectionSteps;
    }

    private function extractVideoUrl() {

        foreach ($this->articleDetail?->getStepsVideos() as $key => $video) {

            // find first dot and to compare first sentence or just compare first 20 chars
            //$len = stripos($video['stepText'], '.');
            //$len = $len == false ? 20 : min($len, 20);
            $len = 50;

            $videoStepText = trim($video['stepText'] ?? '');
            $videoStepText = Str::limit($videoStepText, $len, '');

            // found
            $this->text = str_replace(".  ", ". ", $this->text); //fix a bug
            if(!empty($videoStepText) && str_starts_with($this->text, $videoStepText)){
                $this->videoUrl = $video['src'];
                $video['mappedTo'] = $this->text;
                $video['mapped'] = 'YES';
                $this->articleDetail->setStepVideoItem($video, $key);
                break;
            }
        }

        $this->hasVideo = !empty($this->videoUrl);
    }

    public function hasVideo() : bool {
        return $this->hasVideo;
    }
}
